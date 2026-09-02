(function () {
    'use strict';

    // Открывает предложение, на которое указывает якорь, и подтягивает его во вьюпорт:
    // раскрытие <details> меняет высоту документа, поэтому нативный переход по хешу промахивается.
    function openProposalFromHash() {
        var targetId = window.location.hash.slice(1);
        if (!/^proposal-\d+$/.test(targetId)) return;

        var proposal = document.getElementById(targetId);
        if (!proposal || proposal.tagName !== 'DETAILS' || !proposal.classList.contains('gso-proposal')) return;

        proposal.open = true;

        var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        proposal.scrollIntoView({ block: 'start', behavior: reduced ? 'auto' : 'smooth' });
    }

    window.addEventListener('hashchange', openProposalFromHash);
    document.addEventListener('DOMContentLoaded', openProposalFromHash);
})();
