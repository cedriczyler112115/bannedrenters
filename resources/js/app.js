import $ from 'jquery';
import initializeJqueryConfirm from 'jquery-confirm';
import 'jquery-confirm/dist/jquery-confirm.min.css';

window.$ = window.jQuery = $;
initializeJqueryConfirm(window, $);

document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-password-toggle]');
    if (! button) return;

    const input = document.getElementById(button.dataset.passwordToggle);
    if (! input) return;

    const showing = input.type === 'text';
    input.type = showing ? 'password' : 'text';
    button.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
});

const showRegistrySkeleton = () => {
    const results = document.querySelector('[data-registry-results]');
    const skeleton = document.querySelector('[data-registry-skeleton]');

    if (! results || ! skeleton) return;

    results.classList.add('hidden');
    skeleton.classList.remove('hidden');
};

document.addEventListener('submit', (event) => {
    if (event.target.matches('[data-loading-form]')) showRegistrySkeleton();

    if (event.target.matches('[data-delete-record]')) {
        event.preventDefault();

        const form = event.target;
        const recordName = event.target.dataset.recordName;
        const safeRecordName = $('<span>').text(recordName).html();

        $.confirm({
            title: 'Delete this entry?',
            content: `Delete <strong>${safeRecordName}</strong>? This cannot be undone.`,
            type: 'red',
            theme: 'modern',
            useBootstrap: false,
            boxWidth: 'min(420px, calc(100vw - 32px))',
            alignMiddle: true,
            draggable: false,
            animateFromElement: false,
            animation: 'scale',
            closeAnimation: 'scale',
            backgroundDismiss: false,
            buttons: {
                cancel: {
                    text: 'Cancel',
                },
                deleteEntry: {
                    text: 'Delete',
                    btnClass: 'btn-red',
                    action: () => form.submit(),
                },
            },
        });
    }
});

document.addEventListener('click', (event) => {
    if (event.target.closest('[data-registry-pagination] a')) showRegistrySkeleton();
});

window.addEventListener('pageshow', () => {
    document.querySelector('[data-registry-results]')?.classList.remove('hidden');
    document.querySelector('[data-registry-skeleton]')?.classList.add('hidden');
});

const closeRecordModal = (modal) => {
    modal?.removeAttribute('open');
    document.body.classList.remove('overflow-hidden');
};

document.addEventListener('click', (event) => {
    const modal = event.target.closest('[data-record-modal]');

    if (event.target.closest('[data-modal-close]')) closeRecordModal(modal);

    const summary = event.target.closest('[data-record-modal] > summary');
    if (summary?.parentElement.open) event.preventDefault();
});

document.addEventListener('toggle', (event) => {
    if (! event.target.matches('[data-record-modal]')) return;

    document.body.classList.toggle('overflow-hidden', event.target.open);
}, true);

if (document.querySelector('[data-record-modal][open]')) {
    document.body.classList.add('overflow-hidden');
}

let licenseLightboxTrigger;

const closeLicenseLightbox = () => {
    const lightbox = document.querySelector('[data-license-lightbox]');
    const image = lightbox?.querySelector('[data-lightbox-image]');

    if (! lightbox || lightbox.classList.contains('hidden')) return;

    lightbox.classList.add('hidden');
    lightbox.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');

    if (image) {
        image.src = '';
        image.alt = '';
    }

    licenseLightboxTrigger?.focus();
};

document.addEventListener('click', (event) => {
    const preview = event.target.closest('[data-license-preview]');

    if (preview) {
        const lightbox = document.querySelector('[data-license-lightbox]');
        const image = lightbox?.querySelector('[data-lightbox-image]');

        if (! lightbox || ! image) return;

        licenseLightboxTrigger = preview;
        image.src = preview.dataset.imageSrc;
        image.alt = preview.dataset.imageAlt;
        lightbox.classList.remove('hidden');
        lightbox.classList.add('flex');
        document.body.classList.add('overflow-hidden');
        lightbox.querySelector('[data-lightbox-close]')?.focus();
    }

    if (event.target.closest('[data-lightbox-close]') || event.target.matches('[data-lightbox-backdrop]')) {
        closeLicenseLightbox();
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') closeLicenseLightbox();
});
