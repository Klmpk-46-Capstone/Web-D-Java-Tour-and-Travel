import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';
import { indonesianLocale } from './flatpickr-id';

const formatRupiah = (amount) =>
    'Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(amount || 0);

const formatDate = (date) =>
    date
        ? new Intl.DateTimeFormat('id-ID', {
              day: 'numeric',
              month: 'long',
              year: 'numeric',
          }).format(date)
        : '-';

const formatDateForParam = (date) => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
};

document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-armada-booking]');
    const dateInput = document.getElementById('rentalDate');
    const paxInput = document.getElementById('passengerCount');
    const decreaseButton = document.getElementById('decreasePax');
    const increaseButton = document.getElementById('increasePax');

    if (!root || !dateInput || !paxInput || !decreaseButton || !increaseButton) {
        return;
    }

    const optionButtons = Array.from(root.querySelectorAll('[data-rental-option]'));
    const priceDisplay = document.getElementById('rentalPrice');
    const optionLabelDisplay = document.getElementById('rentalOptionLabel');
    const optionDescriptionDisplay = document.getElementById('rentalOptionDescription');
    const dpPerUnitDisplay = document.getElementById('dpPerUnit');
    const summaryPaxDisplay = document.getElementById('summaryPax');
    const totalDisplay = document.getElementById('displayTotal');
    const dpDisplay = document.getElementById('displayDP');
    const remainingDisplay = document.getElementById('displayRemaining');
    const bookingCta = document.getElementById('continueToBookingForm');
    const csCta = document.getElementById('rentalCsCta');
    const errorDisplay = document.getElementById('bookingFormError');

    const seats = Math.max(0, Number.parseInt(root.dataset.seats, 10) || 0);
    const vehicleName = root.dataset.vehicleName || 'armada';
    const whatsappNumber = root.dataset.waNumber || '';
    const contactUrl = root.dataset.contactUrl || '/#kontak';
    const bookingFormUrl = root.dataset.bookingFormUrl || '';
    const downPaymentRate = 30;

    const state = {
        key: 'daily',
        price: 0,
        label: 'Sewa Harian',
        description: '',
        pax: 0,
        date: null,
    };

    const setActiveOption = (activeButton) => {
        optionButtons.forEach((button) => {
            const isActive = button === activeButton;

            button.classList.toggle('bg-white', isActive);
            button.classList.toggle('text-teal-800', isActive);
            button.classList.toggle('shadow-sm', isActive);
            button.classList.toggle('text-slate-600', !isActive);
            button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });
    };

    const getWhatsAppUrl = (message) =>
        whatsappNumber
            ? `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(message)}`
            : contactUrl;

    const buildBookingFormUrl = () => {
        const url = new URL(bookingFormUrl, window.location.origin);

        if (state.date) {
            url.searchParams.set('rental_date', formatDateForParam(state.date));
        }

        if (state.pax > 0) {
            url.searchParams.set('pax', String(state.pax));
        }

        if (state.key) {
            url.searchParams.set('rental_option', state.key);
        }

        url.searchParams.set('duration', '1');

        return url.toString();
    };

    const buildCsMessage = () => {
        const dateText = state.date ? ` untuk tanggal ${formatDate(state.date)}` : '';

        return `Halo D'Java Tour & Travel, saya ingin bertanya jadwal dan ketersediaan ${vehicleName}${dateText}.`;
    };

    const getTotal = () => (state.pax > 0 ? state.price : 0);
    const getDownPayment = (total) => Math.round((total * downPaymentRate) / 100);

    const showError = (message) => {
        if (!errorDisplay) {
            return;
        }

        errorDisplay.textContent = message;
        errorDisplay.classList.remove('hidden');
    };

    const hideError = () => {
        if (!errorDisplay) {
            return;
        }

        errorDisplay.textContent = '';
        errorDisplay.classList.add('hidden');
    };

    const syncCtaLinks = () => {
        const csUrl = getWhatsAppUrl(buildCsMessage());

        if (csCta) {
            csCta.href = csUrl;
        }

        if (bookingCta) {
            bookingCta.href = buildBookingFormUrl();
        }
    };

    const syncSummary = () => {
        const total = getTotal();
        const downPayment = getDownPayment(total);

        if (summaryPaxDisplay) {
            summaryPaxDisplay.textContent = String(state.pax);
        }

        if (totalDisplay) {
            totalDisplay.textContent = formatRupiah(total);
        }

        if (dpDisplay) {
            dpDisplay.textContent = formatRupiah(downPayment);
        }

        if (remainingDisplay) {
            remainingDisplay.textContent = formatRupiah(total - downPayment);
        }
    };

    const syncPaxControls = () => {
        if (paxInput.value !== String(state.pax)) {
            paxInput.value = String(state.pax);
        }

        decreaseButton.disabled = state.pax <= 0;
        increaseButton.disabled = seats <= 0 || state.pax >= seats;
    };

    const setPax = (nextPax) => {
        const parsed = Number.parseInt(nextPax, 10);
        const safePax = Number.isFinite(parsed) ? parsed : 0;

        state.pax = Math.min(Math.max(safePax, 0), seats);

        syncPaxControls();
        syncSummary();
        syncCtaLinks();

        if (state.pax > 0) {
            hideError();
        }
    };

    const syncOption = () => {
        if (priceDisplay) {
            priceDisplay.textContent = formatRupiah(state.price);
        }

        if (optionLabelDisplay) {
            optionLabelDisplay.textContent = state.label;
        }

        if (optionDescriptionDisplay) {
            optionDescriptionDisplay.textContent = state.description;
        }

        if (dpPerUnitDisplay) {
            dpPerUnitDisplay.textContent = formatRupiah(getDownPayment(state.price));
        }

        syncSummary();
        syncCtaLinks();
    };

    optionButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const price = Number.parseInt(button.dataset.price, 10);

            state.price = Number.isFinite(price) ? price : 0;
            state.label = button.dataset.label || 'Sewa Harian';
            state.description = button.dataset.description || '';
            state.key = button.dataset.key || 'daily';

            setActiveOption(button);
            syncOption();
            hideError();
        });
    });

    const initialOption = optionButtons[0];

    if (initialOption) {
        const initialPrice = Number.parseInt(initialOption.dataset.price, 10);

        state.price = Number.isFinite(initialPrice) ? initialPrice : 0;
        state.label = initialOption.dataset.label || 'Sewa Harian';
        state.description = initialOption.dataset.description || '';
        state.key = initialOption.dataset.key || 'daily';

        setActiveOption(initialOption);
    }

    flatpickr(dateInput, {
        locale: indonesianLocale,
        dateFormat: 'j F Y',
        minDate: 'today',
        allowInput: false,
        disableMobile: true,
        onChange: (selectedDates) => {
            state.date = selectedDates[0] || null;

            if (state.date) {
                hideError();
            }

            syncCtaLinks();
        },
    });

    decreaseButton.addEventListener('click', () => {
        setPax(state.pax - 1);
    });

    increaseButton.addEventListener('click', () => {
        setPax(state.pax + 1);
    });

    paxInput.addEventListener('input', () => {
        setPax(paxInput.value);
    });

    paxInput.addEventListener('blur', () => {
        setPax(paxInput.value);
    });

    if (bookingCta) {
        bookingCta.addEventListener('click', (event) => {
            if (!state.date) {
                event.preventDefault();
                showError('Silakan pilih tanggal sewa terlebih dahulu.');
                dateInput.focus();
                return;
            }

            if (state.pax < 1) {
                event.preventDefault();
                showError('Silakan tambahkan jumlah penumpang minimal 1 Pax.');
                paxInput.focus();
                return;
            }

            hideError();
        });
    }

    syncPaxControls();
    syncOption();
    syncCtaLinks();
});
