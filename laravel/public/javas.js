const courses = [
    { name: 'Bases de données Relationnelle', link: 'Semestre1/BDR/BDR.html'},
    { name: 'Digital Skills', link: 'Semestre1/DS/DS.html' },
    { name: 'Structure de Données en C', link: 'Semestre1/C/C.html' },
    { name: 'Langues étrangères 1', link: 'Semestre1/LE1/LE1.html' },
    { name: 'Réseaux informatiques', link: 'Semestre1/RI/RI.html' },
    { name: 'Théorie des Graphes et Recherche Opérationnel', link: 'Semestre1/ThG_RO/ThG.html' },
    { name: 'Architecture des Ordinateurs et Assembleur', link: 'Semestre1/AO/AO.html' },
    { name: 'Théories des Langages et compilation', link: 'Semestre2/TLC/TLC.html' },
    { name: 'Développement Web', link: 'Semestre2/DW/DW.html' },
    { name: 'Langues étrangères 2', link: 'Semestre2/LE2/LE2.html' },
    { name: 'Modélisation Orientée Objet', link: 'Semestre2/MOO/MOO.html' },
    { name: 'Programmation Orientée Objet Java', link: 'Semestre2/JAVA/JAVA.html' },
    { name: 'Culture & arts & sport skills', link: 'Semestre2/CASS/CASS.html' },
    { name: "Systèmes d'Exploitation et Linux", link: 'Semestre2/SEL/SEL.html' }
];

document.addEventListener('DOMContentLoaded', function() {
    const searchIcon = document.querySelector('.search-icon');
    const searchBox = document.querySelector('.search-box');
    const searchInput = document.querySelector('.search-input');
    const searchResults = document.querySelector('.search-results');

    searchIcon.addEventListener('click', function(e) {
        e.preventDefault();
        searchBox.classList.toggle('active');
        if (searchBox.classList.contains('active')) {
            searchInput.focus();
        }
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-container')) {
            searchBox.classList.remove('active');
            searchResults.classList.remove('active');
        }
    });

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const filteredCourses = courses.filter(course => 
            course.name.toLowerCase().includes(searchTerm)
        );

        searchResults.innerHTML = '';
        if (searchTerm.length > 0) {
            filteredCourses.forEach(course => {
                const div = document.createElement('div');
                div.className = 'result-item';
                div.innerHTML = <a href="${course.link}">${course.name}</a>;
                searchResults.appendChild(div);
            });
            searchResults.classList.add('active');
        } else {
            searchResults.classList.remove('active');
        }
    });
});