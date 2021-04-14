function base64ToArrayBuffer(b64) {
    var byteString = window.atob(b64);
    var byteArray = new Uint8Array(byteString.length);
    for(var i=0; i < byteString.length; i++) {
        byteArray[i] = byteString.charCodeAt(i);
    }

    return byteArray;
}

function strToArrayBuffer(str) {
    var buf = new ArrayBuffer(str.length);

    var bufView = new Uint8Array(buf);
    for (var i = 0, strLen = str.length; i < strLen; i++) {
        bufView[i] = str.charCodeAt(i);
    }
    return buf;
}

function arrayBufferToString(buf) {
    return String.fromCharCode.apply(null, new Uint8Array(buf))
}

function arrayBufferToBase64(buf) {
    return btoa(arrayBufferToString(buf))
}

function base64ToString(b64) {
    return new TextDecoder().decode(base64ToArrayBuffer(b64))
}

async function postData(url = '', data = {}) {
    // Default options are marked with *
    const response = await fetch(url, {
        method: 'POST', // *GET, POST, PUT, DELETE, etc.
        mode: 'cors', // no-cors, *cors, same-origin
        cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
        credentials: 'same-origin', // include, *same-origin, omit
        headers: {
            'Content-Type': 'application/json'
            // 'Content-Type': 'application/x-www-form-urlencoded',
        },
        redirect: 'follow', // manual, *follow, error
        referrerPolicy: 'no-referrer', // no-referrer, *no-referrer-when-downgrade, origin, origin-when-cross-origin, same-origin, strict-origin, strict-origin-when-cross-origin, unsafe-url
        body: JSON.stringify(data) // body data type must match "Content-Type" header
    });
    return response.json(); // parses JSON response into native JavaScript objects
}


class Crypto {
    constructor({
        publicEncryptionKeyLink: publicEncryptionKeyLink,
        publicSigningKeyLink: publicSigningKeyLink,
        signingLink: signingLink,
        savingLink: savingLink
    }) {
        this.aesKey = this.generateAesKey()
        this.publicEncryptionKey = this.importPublicEncryptionKey(publicEncryptionKeyLink)
        this.publicSigningKey = this.importPublicSigningKey(publicSigningKeyLink)
        this.signingLink = signingLink
        this.savingLink = savingLink
        this.iv = window.crypto.getRandomValues(new Uint8Array(12));
        this.modalConsole = $('#console')
        this.modalProgress = $('#progressbar')
        this.modalOutput = $('#encrypted')
    }
    async processVote(vote) {
        const myModal = $('#myModal')
        myModal.modal({ keyboard: false, backdrop: 'static'})
        myModal.modal('show')
        this.modalConsole.append('Encrypting vote using AES-GCM ...\n')
        const encryptedVote = await this.aesEncrypt(vote).then((result) => {
            this.modalProgress.css('width', '10%')
            this.modalConsole.append('Vote encrypted (see output below) ...\n')
            this.modalOutput.append('-------- AES encrypted vote --------'+ result + '\n')
            return result
        })
        this.modalConsole.append('Encrypting AES key with RSA public key ...\n')
        const wrappedKey = await this.getWrappedKey().then((result) => {
            this.modalConsole.append('AES key encrypted (see output below) ... \n')
            this.modalOutput.append('-------- RSA encrypted secret key -------- '+ result + '\n')
            this.modalProgress.css('width', '20%')
            return result
        })
        // console.log('encryptedVote')
        // console.log(encryptedVote)
        // console.log('wrappedKey')
        // console.log(wrappedKey)


        let message = wrappedKey;
        this.modalConsole.append('Applying random blinding factor ...\n')
        const { blinded, r } = await this.blind(message);
        // console.log(blinded.toString(16));
        this.modalProgress.css('width', '30%')

        this.modalConsole.append('Requesting signature from the server ...\n')
        const signed = await postData(this.signingLink, {message: blinded.toString(16)}).then((result) => {
            return new window.jsbn.BigInteger(result.message, 16);
        })
        // console.log(signed.toString(16));
        this.modalProgress.css('width', '40%')
        this.modalConsole.append('Removing blinding factor ...\n')
        const unblinded = await this.unblind({ signed, r});
        // console.log(unblinded.toString(16));
        this.modalOutput.append('-------- Signature -------- '+ unblinded.toString(16) + '\n')
        this.modalConsole.append('Verifying signature integrity ...\n')
        this.modalProgress.css('width', '50%')

        const verified = await this.verify({unblinded, message})
        this.modalProgress.css('width', '60%')
        if (verified) {
            this.modalConsole.append('Signature and encrypted vote match ...\n')
        } else {
            this.modalConsole.append('Signature doesn\'t match the encrypted vote, stopping ...\n')
            throw new Error('Signature verification failed.')
        }
        postData(this.savingLink, { ballot: encryptedVote, key: wrappedKey, signature: unblinded.toString(16) }).then((result) => {
            console.log(result)
        })


    }

    messageToHash(message) {
        const messageHash = window.jsSha256(message);
        // console.log("hash");console.log(messageHash);
        return messageHash;
    }

    messageToHashInt(message) {
        const messageHash = this.messageToHash(message);
        const messageBig = new window.jsbn.BigInteger(messageHash, 16);
        // console.log("messageHashInt"); console.log(messageBig.toString());
        return messageBig;
    }

    async blind(message) {
        const messageHash = this.messageToHashInt(message);

        const key = await this.publicSigningKey
        const N = key.parts.N
        const E = key.parts.E
        // console.log('N')
        // console.log(key.parts.N.toString(16))

        const bigOne = new window.jsbn.BigInteger('1');
        let gcd;
        let r;
        do {
            // r = new window.jsbn.BigInteger(window.jsbn.SecureRandom(64)).mod(N);
            r = new window.jsbn.BigInteger(window.crypto.getRandomValues(new Uint8Array(64))).mod(N);
            gcd = r.gcd(N);
        } while (
            !gcd.equals(bigOne) ||
            r.compareTo(N) >= 0 ||
            r.compareTo(bigOne) <= 0
            );
        const blinded = messageHash.multiply(r.modPow(E, N)).mod(N);
        return {
            blinded,
            r,
        };
    }

    async unblind({ signed, r}) {
        r = new window.jsbn.BigInteger(r.toString());
        const key = await this.publicSigningKey;
        const N = key.parts.N
        signed = new window.jsbn.BigInteger(signed.toString());
        // console.log('unblinding signed')
        // console.log(signed.toString(16))
        const unblinded = signed.multiply(r.modInverse(N)).mod(N);
        return unblinded;
    }

    async verify({ unblinded, message }) {
        const key = await this.publicSigningKey
        unblinded = new window.jsbn.BigInteger(unblinded.toString());
        const messageHash = this.messageToHashInt(message);
        const N = key.parts.N
        const E = key.parts.E

        const originalMsg = unblinded.modPow(E, N);
        // console.log('originalMsg')
        // console.log(originalMsg.toString(16))
        const result = messageHash.equals(originalMsg);
        return result;
    }

    async aesEncrypt(message) {
        console.log(message)
        const encoded = new TextEncoder().encode(JSON.stringify(message))
        let encrypted = window.crypto.subtle.encrypt(
            {
                name: "AES-GCM",
                iv: this.iv,
                tagLength: 128
            },
            await this.aesKey,
            encoded
        );
        return arrayBufferToBase64(await encrypted)
    }

    async getWrappedKey() {
        const encodedKey = window.crypto.subtle.exportKey('raw', await this.aesKey)
        let data = {
        	'key': arrayBufferToBase64(await encodedKey),
        	'iv': arrayBufferToBase64(this.iv)
        }
        return this.rsaEncrypt(data)
    }

    async rsaEncrypt(message) {
        const encoded = new TextEncoder().encode(JSON.stringify(message))
        const encrypted = window.crypto.subtle.encrypt(
            {name: "RSA-OAEP"},
            await this.publicEncryptionKey,
            encoded
        )
        return arrayBufferToBase64(await encrypted)
    }

    /** extract **/

    async generateAesKey() {
        return window.crypto.subtle.generateKey(
            {
                name: "AES-GCM",
                length: 256,
            },
            true,
            ["encrypt", "decrypt"]
        )
    }

    async importPublicEncryptionKey(url) {
        const response = await postData(url)
        console.log('Public Singing key')
        console.log(response)
        const buffer = this.parsePublicKey(response)
        return window.crypto.subtle.importKey(
            "spki",
            buffer,
            {
                name: "RSA-OAEP",
                hash: "SHA-256"
            },
            true,
            ["encrypt", "wrapKey"]
        );


    }

    async importPublicSigningKey(url) {
        const response = await postData(url)
        const buffer = this.parsePublicKey(response.key)
        const key = window.crypto.subtle.importKey(
            "spki",
            buffer,
            {
                name: "RSASSA-PKCS1-v1_5",
                // name: "RSA-PSS",
                hash: "SHA-1"
            },
            true,
            ["verify"]
        );
        const parts = {N: new window.jsbn.BigInteger(response.n, 16), E: new window.jsbn.BigInteger(response.e, 16)}
        return {
            key: await key,
            parts: parts
        }
    }

    parsePublicKey(pem) {
        // fetch the part of the PEM string between header and footer
        const pemHeader = "-----BEGIN PUBLIC KEY-----";
        const pemFooter = "-----END PUBLIC KEY-----";
        const pemContents = pem.substring(pemHeader.length, pem.length - pemFooter.length);
        // base64 decode the string to get the binary data
        const binaryDerString = window.atob(pemContents);
        // convert from a binary string to an ArrayBuffer
        return strToArrayBuffer(binaryDerString);
    }
}

export default Crypto