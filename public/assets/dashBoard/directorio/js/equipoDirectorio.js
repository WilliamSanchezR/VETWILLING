// Giro de las tarjetas del equipo con toque o teclado. Con mouse se voltean por hover (CSS).
document.addEventListener('DOMContentLoaded', () => {
    const conMouse = window.matchMedia('(hover: hover)').matches;

    document.querySelectorAll('.dir-equipo .team-flip').forEach((boton) => {
        boton.addEventListener('click', (evento) => {
            if (conMouse && evento.detail !== 0) return;

            const tarjeta = boton.closest('.team-card');
            const volteada = tarjeta.classList.toggle('is-flipped');
            tarjeta.querySelector('.team-front .team-flip').setAttribute('aria-expanded', String(volteada));

            if (evento.detail === 0) {
                const cara = volteada ? '.team-back .team-flip' : '.team-front .team-flip';
                tarjeta.querySelector(cara).focus({ preventScroll: true });
            }
        });
    });
});
