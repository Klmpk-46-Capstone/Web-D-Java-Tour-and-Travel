@php
    $package = $booking->package;
    $trip = $booking->trip;
    $latestTransaction = $booking->transactions->first();

    $paymentStatus = strtolower((string) $booking->payment_status);
    $bookingStatus = strtolower((string) $booking->booking_status);

    $isCancelled = in_array($bookingStatus, ['cancelled', 'canceled'], true);
    $isRefundRequested = $bookingStatus === 'refund_requested';

    $isTripCompleted = in_array(
        $bookingStatus,
        ['completed', 'finished'],
        true
    );
    $isCompleted = $isTripCompleted;
    $isConfirmed = in_array($bookingStatus, ['confirmed', 'completed', 'finished'], true);
    $hasReviewed = (bool) $booking->review;

    $paidAmount = (int) $booking->transactions
        ->filter(fn ($transaction) => in_array(
            strtolower((string) $transaction->status),
            ['success', 'paid', 'settled', 'settlement', 'completed', 'capture'],
            true
        ))
        ->sum('amount');

    $totalPrice = (int) $booking->total_price;
    $isPaid = $totalPrice > 0 && $paidAmount >= $totalPrice;

    $isWaitingDp = ! $isPaid
        && in_array(
            $paymentStatus,
            ['pending', 'unpaid', 'waiting_dp'],
            true
        )
        && !$isCancelled
        && !$isRefundRequested;

    $isWaitingPelunasan = ! $isPaid
        && $paymentStatus === 'partial'
        && !$isCancelled
        && !$isRefundRequested
        && !$isCompleted;

    $statusLabel = match (true) {
        $isCancelled => 'Cancelled',
        $isRefundRequested => 'Awaiting Refund',
        $isCompleted => 'Completed',
        $isPaid => 'Paid',
        $paymentStatus === 'partial' => 'DP Paid',
        $isConfirmed => 'Confirmed',
        default => 'Awaiting DP',
    };

    $statusClass = match (true) {
        $isCancelled, $isRefundRequested => 'bg-rose-100 text-rose-800',
        $isCompleted, $isPaid => 'bg-blue-100 text-blue-800',
        $paymentStatus === 'partial' || $isConfirmed => 'bg-teal-100 text-teal-800',
        default => 'bg-orange-100 text-orange-900',
    };

    $downPayment = (int) round($totalPrice * 0.30);
    $remainingAmount = max(0, $totalPrice - $paidAmount);

    if ($isWaitingDp) {
        $displayAmount = $downPayment;
        $amountLabel = '30% DP Required';
        $amountNote = 'Total: Rp ' . number_format($totalPrice, 0, '.', ',');
    } elseif ($isPaid) {
        $displayAmount = $paidAmount;
        $amountLabel = 'Amount Paid';
        $amountNote = 'Total: Rp ' . number_format($totalPrice, 0, '.', ',');
    } elseif ($isCompleted) {
        $displayAmount = $totalPrice;
        $amountLabel = 'Total Payment';
        $amountNote = $remainingAmount > 0
            ? 'Paid: Rp ' . number_format($paidAmount, 0, '.', ',')
            : 'No outstanding balance';
    } else {
        $displayAmount = $remainingAmount;
        $amountLabel = 'Remaining Payment';
        $amountNote = 'Recorded DP: Rp ' . number_format($paidAmount, 0, '.', ',');
    }

    $departureDate = $trip?->departure_date;
    $returnDate = $trip?->return_date;

    $dateText = $departureDate
        ? \Illuminate\Support\Carbon::parse($departureDate)->translatedFormat('d M Y')
        : $booking->created_at?->translatedFormat('d M Y');

    if ($returnDate) {
        $dateText .= ' - ' . \Illuminate\Support\Carbon::parse($returnDate)->translatedFormat('d M Y');
    }

    $locationText = $booking->pickup_location ?: 'Location not specified';

    $bookingTitle = $package?->name
        ?? $booking->selected_armada
        ?? 'D’Java Tour Reservation';

    $imageUrl = $package?->image_url;
    $imageUrlIsSafe = $imageUrl && in_array(
        parse_url($imageUrl, PHP_URL_SCHEME),
        ['https', 'http'],
        true
    );
@endphp

<article
    id="booking-{{ $booking->id }}"
    data-status-url="{{ localized_route('pembayaran.cek-status-otomatis', $booking->booking_code) }}"
    data-payment-status="{{ $paymentStatus }}"
    class="rounded-2xl border border-blue-100 bg-white p-4 shadow-sm transition hover:shadow-md sm:p-5 xl:p-6"
>
    <div class="flex flex-col gap-5 xl:flex-row xl:items-center">
        <div class="flex min-w-0 flex-1 items-start gap-4">
            <div class="h-20 w-24 shrink-0 overflow-hidden rounded-xl bg-slate-100 sm:h-24 sm:w-28">
                @if ($imageUrlIsSafe)
                    <img
                        src="{{ $imageUrl }}"
                        alt="{{ $bookingTitle }}"
                        loading="lazy"
                        referrerpolicy="no-referrer"
                        class="h-full w-full object-cover"
                    >
                @else
                    <div class="flex h-full w-full items-center justify-center text-teal-700">
                        <svg
                            class="h-8 w-8"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                            aria-hidden="true"
                        >
                            <rect x="3" y="4" width="18" height="16" rx="2"></rect>
                            <circle cx="8.5" cy="9" r="1.5"></circle>
                            <path d="m21 15-5-5L5 20"></path>
                        </svg>
                    </div>
                @endif
            </div>

            <div class="min-w-0 flex-1">
                <div class="mb-2 flex flex-wrap items-center gap-2">
                    <span class="rounded-md bg-blue-50 px-2 py-1 font-mono text-xs tracking-wide text-slate-600">
                        {{ e($booking->booking_code) }}
                    </span>

                    <span class="booking-status-badge inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">
                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                        {{ $statusLabel }}
                    </span>
                </div>

                <h2 class="break-words text-lg font-bold leading-snug text-slate-900 sm:text-xl">
                    {{ e($bookingTitle) }}
                    @if ($booking->selected_armada && $package)
                        <span class="font-medium text-slate-600">— {{ e($booking->selected_armada) }}</span>
                    @endif
                </h2>

                <p class="mt-1.5 text-sm leading-6 text-slate-600 sm:text-base">
                    {{ $dateText }}
                    <span aria-hidden="true">•</span>
                    {{ e($booking->pax) }} Participants
                    <span aria-hidden="true">•</span>
                    {{ e($locationText) }}
                </p>
            </div>
        </div>

        <div class="flex flex-col gap-4 border-t border-slate-100 pt-4 xl:w-64 xl:shrink-0 xl:border-l xl:border-t-0 xl:pl-5 xl:pt-0">
            <div class="xl:text-right">
                <p class="text-xs font-semibold text-slate-600">{{ $amountLabel }}</p>

                <p class="mt-1 text-2xl font-extrabold tracking-tight text-teal-800 sm:text-3xl">
                    Rp {{ number_format($displayAmount, 0, '.', ',') }}
                </p>

                <p class="mt-1 text-xs font-semibold text-slate-500">
                    {{ $amountNote }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2 xl:justify-end">
                @if ($isWaitingDp)
                    <a
                        href="{{ localized_route('pembayaran.mulai', [
                            'bookingCode' => $booking->booking_code,
                            'stage' => 'dp',
                        ]) }}"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-teal-700 px-3.5 py-2 text-sm font-semibold text-white hover:bg-teal-800"
                    >
                        Pay DP
                    </a>
                @elseif ($isWaitingPelunasan)
                    <a
                        href="{{ localized_route('pembayaran.mulai', [
                            'bookingCode' => $booking->booking_code,
                            'stage' => 'pelunasan',
                        ]) }}"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-teal-700 px-3.5 py-2 text-sm font-semibold text-white hover:bg-teal-800"
                    >
                        Pay Balance
                    </a>
                @elseif ($isConfirmed && !$isCompleted)
                    @if ($booking->customer_phone)
                        <a
                            href="https://wa.me/{{ preg_replace('/\D+/', '', $booking->customer_phone) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-green-500 px-3.5 py-2 text-sm font-semibold text-white hover:bg-green-600"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.4 19.4 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7l.5 2.8a2 2 0 0 1-.6 1.9L7.2 10a16 16 0 0 0 6 6l1.6-1.8a2 2 0 0 1 1.9-.6l2.8.5a2 2 0 0 1 1.7 2.8Z"></path>
                            </svg>
                            Contact
                        </a>
                    @endif
                @endif

                @if ($isPaid && $isCompleted && !$isCancelled && !$isRefundRequested)
                    @if ($hasReviewed)
                        <span class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-emerald-50 px-3.5 py-2 text-sm font-semibold text-emerald-700">
                            ✓ Review Submitted
                        </span>
                    @else
                        <a
                            href="{{ localized_route('customer.bookings.rating', [
                                'bookingCode' => $booking->booking_code
                            ]) }}"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-amber-500 px-3.5 py-2 text-sm font-semibold text-white hover:bg-amber-600"
                        >
                            ★ Rate Trip
                        </a>
                    @endif
                @endif

                @if ($isCompleted && !$isCancelled && !$isRefundRequested)
                    <a
                        href="{{ localized_route('customer.bookings.index') }}"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-teal-700 px-3.5 py-2 text-sm font-semibold text-white hover:bg-teal-800"
                    >
                        Book Again
                    </a>
                @endif

                <a
                    href="{{ localized_route('customer.bookings.detail', [
                        'bookingCode' => $booking->booking_code,
                    ]) }}"
                    class="inline-flex h-10 items-center justify-center rounded-lg bg-blue-100 px-3.5 py-2 text-sm font-semibold text-slate-800 transition hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                    Booking Details
                </a>
            </div>
        </div>
    </div>
</article>
