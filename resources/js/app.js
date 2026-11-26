// Progressive enhancements only: every page works without JavaScript.

document.addEventListener('submit', (event) => {
    const form = event.target;
    const message = form.dataset.confirm;
    if (message && !window.confirm(message)) {
        event.preventDefault();
        return;
    }
    form.querySelectorAll('button[type="submit"]').forEach((button) => {
        button.disabled = true;
    });
});

document.querySelectorAll('[data-dismiss]').forEach((button) => {
    button.addEventListener('click', () => button.closest('[role="status"]')?.remove());
});

document.querySelectorAll('[data-menu-toggle]').forEach((button) => {
    const menu = document.getElementById(button.getAttribute('aria-controls'));
    button.addEventListener('click', () => {
        const open = button.getAttribute('aria-expanded') === 'true';
        button.setAttribute('aria-expanded', String(!open));
        menu?.classList.toggle('hidden', open);
    });
});

// Handwritten signature: draw on the canvas, send it as a PNG data URL.
document.querySelectorAll('[data-signature-form]').forEach((form) => {
    const canvas = form.querySelector('[data-signature-canvas]');
    const input = form.querySelector('[data-signature-input]');
    const submit = form.querySelector('[data-signature-submit]');
    const context = canvas.getContext('2d');
    let drawing = false;
    let strokes = 0;

    context.lineWidth = 3;
    context.lineCap = 'round';
    context.lineJoin = 'round';
    context.strokeStyle = '#0f172a';

    const point = (event) => {
        const box = canvas.getBoundingClientRect();
        return [(event.clientX - box.left) * (canvas.width / box.width), (event.clientY - box.top) * (canvas.height / box.height)];
    };

    canvas.addEventListener('pointerdown', (event) => {
        drawing = true;
        canvas.setPointerCapture(event.pointerId);
        context.beginPath();
        context.moveTo(...point(event));
    });
    canvas.addEventListener('pointermove', (event) => {
        if (!drawing) {
            return;
        }
        context.lineTo(...point(event));
        context.stroke();
    });
    const stop = () => {
        if (drawing) {
            drawing = false;
            strokes++;
            submit.disabled = false;
        }
    };
    canvas.addEventListener('pointerup', stop);
    canvas.addEventListener('pointercancel', stop);

    form.querySelector('[data-signature-clear]').addEventListener('click', () => {
        context.clearRect(0, 0, canvas.width, canvas.height);
        strokes = 0;
        submit.disabled = true;
    });

    form.addEventListener('submit', (event) => {
        if (strokes === 0) {
            event.preventDefault();
            return;
        }
        input.value = canvas.toDataURL('image/png');
    }, { capture: true });
});
