async function digestMessage(message) {
	const encoder = new TextEncoder();
	const data = encoder.encode(message);
	const hash = await crypto.subtle.digest('SHA-256', data);
	return hash;
	}

	function messageToHash(message) {
	const messageHash = jsSha256(message);
	console.log("hash: " + messageHash);
	return messageHash;
	}

	function messageToHashInt(message) {
	const messageHash = messageToHash(message);
	const messageBig = new BigInteger(messageHash, 16);
	return messageBig;
	}

	function blind({ message, key, N, E }) {
	const messageHash = messageToHashInt(message);
	console.log("messageHashInt: " + messageHash.toString());
	N = key ? key.keyPair.n : new BigInteger(N.toString());
	E = key
		? new BigInteger(key.keyPair.e.toString())
		: new BigInteger(E.toString());

	const bigOne = new BigInteger('1');
	let gcd;
	let r;
	do {
		r = new BigInteger(secureRandom(64)).mod(N);
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

	function unblind({ signed, key, r, N }) {
	r = new BigInteger(r.toString());
	N = key ? key.keyPair.n : new BigInteger(N.toString());
	signed = new BigInteger(signed.toString());
	const unblinded = signed.multiply(r.modInverse(N)).mod(N);
	return unblinded;
	}

		// Example POST method implementation:
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

	function importPublicKey(pem) {
		// fetch the part of the PEM string between header and footer
		const pemHeader = "-----BEGIN PUBLIC KEY-----";
		const pemFooter = "-----END PUBLIC KEY-----";
		const pemContents = pem.substring(pemHeader.length, pem.length - pemFooter.length);
		// base64 decode the string to get the binary data
		const binaryDerString = window.atob(pemContents);
		// convert from a binary string to an ArrayBuffer
		const binaryDer = strToArrayBuffer(binaryDerString);

		return window.crypto.subtle.importKey(
		"spki",
		binaryDer,
		{
			name: "RSA-OAEP",
			hash: "SHA-256"
		},
		true,
		["encrypt", "wrapKey"]
		);
	}

	function importPrivateKey(pem) {
		// fetch the part of the PEM string between header and footer
		const pemHeader = "-----BEGIN PRIVATE KEY-----";
		const pemFooter = "-----END PRIVATE KEY-----";
		const pemContents = pem.substring(pemHeader.length, pem.length - pemFooter.length);
		// base64 decode the string to get the binary data
		const binaryDerString = window.atob(pemContents);
		// convert from a binary string to an ArrayBuffer
		const binaryDer = strToArrayBuffer(binaryDerString);

		return window.crypto.subtle.importKey(
		"pkcs8",
		binaryDer,
		{
			name: "RSA-OAEP",
			hash: "SHA-256"
		},
		true,
		["decrypt", "unwrapKey"]
		);
	}


	/*
	Wrap the given key.
	*/
	async function wrapCryptoKey(keyToWrap, wrappingKey) {
	// get the key encryption key
		let wrappedKey = await window.crypto.subtle.wrapKey(
			"raw",
			keyToWrap,
			wrappingKey,
			{
				name: "RSA-OAEP",
				hash: {
					name: "SHA-256"
				},
			}
		);
		return wrappedKey;

	}

	async function unwrapCryptoKey(wrappedKey) {
		let privateKeyString = await getPrivateKey();
		let privateKey = await importPrivateKey(privateKeyString)
		let unwrappedKey = await window.crypto.subtle.unwrapKey(
			'raw',
			wrappedKey,
			privateKey,
			{
				name: "RSA-OAEP",
				hash: {
					name: "SHA-256"
				},
			},
			{
				name: "AES-GCM",
				length: 256,
			},
			true,
			["decrypt"]
		);
		return unwrappedKey;
	}

	async function getRandomKey() {
		let secretKey = await window.crypto.subtle.generateKey(
		{
			name: "AES-GCM",
			length: 256,
		},
		true,
		["encrypt", "decrypt"]
		)
		return secretKey;
	}

	async function getKeys() {
		let string = "Hello World!";
		let privateKey = await getPrivateKey()
		let publicKey = await getPublicKey()
		let wrappingKey = await importPublicKey(publicKey)
		let AESkey = await getRandomKey()

		return {'private': privateKey, 'public': publicKey, 'publicCryptoKey': wrappingKey, 'AESkey': AESkey};
	}

	async function AESencrypt({'key': key, 'message': message}) {
		let enc = new TextEncoder();
		let encoded = enc.encode(message);
		iv = window.crypto.getRandomValues(new Uint8Array(12));
		let encrypted = await window.crypto.subtle.encrypt(
			{
				name: "AES-GCM",
				iv: iv,
				tagLength: 128
			},
			key,
			encoded
		);
		return arrayBufferToBase64(encrypted)
	}

	async function AESdecrypt({'key': key, 'ciphertext': ciphertext, 'iv': initVector}) {
		let decoded = strToArrayBuffer(atob(ciphertext));
		let decrypted = await window.crypto.subtle.decrypt(
		{
			name: "AES-GCM",
			iv: initVector
			},
			key,
			decoded
		);
		let string =  new TextDecoder().decode(decrypted);
		return string;
	}

	async function RSAencrypt({'key': key, 'message': message}) {
		let encrypted = await window.crypto.subtle.encrypt(
			{
				name: "RSA-OAEP",
			},
			key,
			message
		)
		return encrypted
	}

