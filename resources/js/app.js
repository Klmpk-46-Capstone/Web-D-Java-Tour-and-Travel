// D'Java Tour & Travel Global JS

// Hidden Admin Gateway Shortcut Trigger
// Trigger: Ctrl + Shift + L (Windows/Linux) or Cmd + Shift + L (macOS)
window.addEventListener('keydown', function (e) {
    const isModifier = e.ctrlKey || e.metaKey;
    if (isModifier && e.shiftKey && (e.key === 'L' || e.key === 'l' || e.code === 'KeyL')) {
        e.preventDefault();
        
        // Visual indicator or subtle transition effect before redirect
        console.log('[D\'Java Tour] Hidden Admin Gateway Triggered');
        
        const toast = document.createElement('div');
        toast.className = 'fixed bottom-5 right-5 z-[9999] flex items-center gap-3 rounded-xl bg-slate-900/95 px-5 py-3.5 text-white shadow-2xl backdrop-blur border border-teal-500/40 text-sm font-medium animate-bounce';
        toast.innerHTML = '<span class="text-teal-400 font-bold">✦ SECURE GATEWAY:</span> Mengalihkan ke CMS Ops Console...';
        document.body.appendChild(toast);

        setTimeout(() => {
            window.location.href = '/admin/gate-auth';
        }, 400);
    }
});
