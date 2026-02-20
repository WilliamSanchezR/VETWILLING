
const precios = {
    mensual: {
        basico: 0,
        pro: 29,
        enterprise: 99
    },
    anual: {
        basico: 0,
        pro: 23,
        enterprise: 79
    }
};

let billingActual = 'mensual';

function toggleBilling(tipo, el) {
    billingActual = tipo;
    document.querySelectorAll('.billing-label').forEach(l => l.classList.remove('active'));
    el.classList.add('active');

    const p = precios[tipo];

    document.getElementById('precio-basico').textContent = p.basico;
    document.getElementById('precio-pro').textContent = p.pro;
    document.getElementById('precio-enterprise').textContent = p.enterprise;

    const ahorroBasico = document.getElementById('ahorro-basico');
    const ahorroProEl = document.getElementById('ahorro-pro');
    const ahorroEntEl = document.getElementById('ahorro-enterprise');

    if (tipo === 'anual') {
        ahorroBasico.textContent = '';
        ahorroProEl.textContent = '✦ Ahorras $72 al año';
        ahorroEntEl.textContent = '✦ Ahorras $240 al año';
    } else {
        ahorroBasico.textContent = '';
        ahorroProEl.textContent = '';
        ahorroEntEl.textContent = '';
    }

    // Animate numbers
    document.querySelectorAll('.precio-numero').forEach(el => {
        el.style.animation = 'none';
        el.offsetHeight;
        el.style.animation = 'fadeUp 0.4s ease';
    });
}