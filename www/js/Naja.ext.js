class LoadingIndicatorExtension {
	constructor() {
		this.defaultLoadingIndicator = '.ajax-spinner';
		this.defaultLoadingIndicatorBackdrop = '.ajax-spinner-backdrop';
	}
	initialize(naja) {
		this.speed = 2000;
		this.hidden = false;
		naja.uiHandler.addEventListener('interaction', this.locateLoadingIndicator.bind(this));
		naja.addEventListener('start', this.showLoader.bind(this));
		naja.addEventListener('complete', this.hideLoader.bind(this));
	  }
	locateLoadingIndicator({detail}) {
		const loadingIndicator = detail.element.closest('.ajax-spinner');
		detail.options.loadingIndicator = loadingIndicator || this.defaultLoadingIndicator;
		detail.options.loadingIndicatorBackdrop = this.defaultLoadingIndicatorBackdrop;
	}
	showLoader({detail}) {
		$(detail.options.loadingIndicator).fadeIn();
		$(detail.options.loadingIndicatorBackdrop).fadeIn();
	}
	hideLoader({detail}) {
		$(detail.options.loadingIndicator).fadeOut(this.speed);
		$(detail.options.loadingIndicatorBackdrop).fadeOut(this.speed);
	}
}

class ModalExtension {
	initialize(naja) {
		naja.addEventListener('success', this.openModal.bind(this));
	}
	openModal(event) {
		let payload = event.detail.payload;

		if (payload === null || !payload.hasOwnProperty('modalId')) {
			return;
		}
		let modalId = payload.modalId;
		let showModal = payload.showModal;
		if (showModal === undefined || showModal === false) {
			return;
		}
		$("#" + modalId).modal('show');
	}
}

class ForceRedirectExtension {
	initialize(naja) {
		naja.addEventListener('complete', this.detectForceRedirect.bind(this));
	}
	detectForceRedirect({detail}) {
		if (detail.payload.forceRedirect) {
			naja.redirectHandler.makeRedirect(detail.url, true, detail.options)
		}
	}
}

class ValidateVotingForm {
	initialize(naja) {
		naja.addEventListener('init', this.onChange.bind(this));
		naja.uiHandler.addEventListener('interaction', this.validateForm.bind(this));
	}
	onChange() {
		const that = this
		$('input[data-nette-rules]').on('change', function (e) {
			const parent = $(e.target).parents('fieldset');
			parent.find('input').each((i, el) => {
				el.setCustomValidity(Nette.validateControl(el) ? '' : 'invalid')
			})
			that.showErrors()
		});
	}
	showErrors() {
		for (let error of Nette.formErrors) {
			$(error.element).parents('fieldset').next().text(error.message)
		}
	}
	validateForm(event) {
		$('form input[type=submit]').attr('disabled', true)
		const { element, originalEvent } = event.detail;
		Nette.formErrors = [];
		for (let el of element.form.getElementsByTagName('input')) {
			if (el.dataset.netteRules !== undefined) {
				el.setCustomValidity(Nette.validateControl(el) ? '' : 'invalid')
			}
		}

		if (originalEvent) {
			originalEvent.stopImmediatePropagation();
			originalEvent.preventDefault();
		}
		event.preventDefault();

		if (Nette.formErrors.length) {
			$(element.form).addClass('was-validated')
			this.showErrors();
			toastr.error('There were errors in the form')
			$('form input[type=submit]').attr('disabled', false)
			return;
		}

		toastr.success('Form is being encrypted')

		const $form = $('form')

		let res = {questions: {}}
		$form.find('fieldset').each( function () {
			let answers = {}
			let qId;
			$.each($(this).serializeArray(), function () {
				let parts = this.name.split(/[[\]]/)
				if (qId === undefined) {
					qId = parts[1]
				}
				answers[this.value] = $('input[value='+ this.value +']').next().text()
			})
			if (qId !== undefined) {
				res.questions[qId] = answers;
			}

		})
		res.electionId = $form.find('input[name=electionId]').val()
		res.timestamp = new Date().getTime()
		console.log(res)


		if (typeof window.crypt === "undefined") {
			throw new Error('Encryption script is not loaded')
		}

		window.crypt.processVote(res).catch((error) => {
			throw new Error('encryption processing error: '+ error)
		}).then((result) => {
			$('#myModal #closeButton').attr('disabled', false)
		})
	}
}