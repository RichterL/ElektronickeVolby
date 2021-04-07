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