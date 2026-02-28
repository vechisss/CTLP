(function () {
    document.querySelectorAll('.pwd-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var wrap = btn.closest('.password-wrap');
            var input = wrap.querySelector('input');
            var isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            btn.textContent = isHidden ? '隐藏' : '显示';
            btn.setAttribute('aria-label', isHidden ? '隐藏密码' : '显示密码');
        });
    });
})();
