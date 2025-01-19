document.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', e => {
        e.preventDefault();
        const href = link.getAttribute('href');
        document.body.style.opacity = '0';
        document.body.style.transform = 'scale(0.98)';
        document.body.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
        
        setTimeout(() => {
            window.location.href = href;
        }, 300);
    });
});

window.addEventListener('load', () => {
    document.body.style.opacity = '0';
    document.body.style.transform = 'scale(0.98)';
    document.body.style.transition = 'opacity 0.5s ease, transform 0.2s ease';
    
    requestAnimationFrame(() => {
        document.body.style.opacity = '1';
        document.body.style.transform = 'scale(1)';
    });
});

document.querySelectorAll('input, select').forEach(input => {
    input.classList.add('form-input');
});