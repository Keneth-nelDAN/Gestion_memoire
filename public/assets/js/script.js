// Désactiver clic droit
document.addEventListener("contextmenu", function(e){
    e.preventDefault();
});

// Désactiver certaines touches
document.addEventListener("keydown", function(e){

    // F12
    if(e.key === "F12"){
        e.preventDefault();
    }

    // Ctrl + U
    if(e.ctrlKey && e.key.toLowerCase() === "u"){
        e.preventDefault();
    }

    // Ctrl + S
    if(e.ctrlKey && e.key.toLowerCase() === "s"){
        e.preventDefault();
    }

    // Ctrl + P
    if(e.ctrlKey && e.key.toLowerCase() === "p"){
        e.preventDefault();
    }

    // Ctrl + C
    if(e.ctrlKey && e.key.toLowerCase() === "c"){
        e.preventDefault();
    }

    // Ctrl + X
    if(e.ctrlKey && e.key.toLowerCase() === "x"){
        e.preventDefault();
    }

    // Ctrl + A
    if(e.ctrlKey && e.key.toLowerCase() === "a"){
        e.preventDefault();
    }

    // Ctrl + Shift + I
    if(
        e.ctrlKey &&
        e.shiftKey &&
        e.key.toLowerCase() === "i"
    ){
        e.preventDefault();
    }

    // Ctrl + Shift + J
    if(
        e.ctrlKey &&
        e.shiftKey &&
        e.key.toLowerCase() === "j"
    ){
        e.preventDefault();
    }
});

document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll("a.ajax-like").forEach(function(link) {
        link.addEventListener("click", function(event) {
            event.preventDefault();

            var url = new URL(this.href, window.location.origin);
            url.searchParams.set('ajax', '1');

            var currentLink = this;
            fetch(url.toString(), {
                credentials: 'same-origin'
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (!data || typeof data.totalLikes === 'undefined') {
                    return;
                }

                var icon = currentLink.querySelector('i.fa-heart');
                if (icon) {
                    if (data.liked) {
                        icon.classList.add('liked');
                    } else {
                        icon.classList.remove('liked');
                    }
                }

                currentLink.innerHTML = '<i class="fa-solid fa-heart ' + (data.liked ? 'liked' : '') + '"></i> ' + data.totalLikes;
                currentLink.setAttribute('data-id', data.idAM);
            })
            .catch(function(error) {
                console.error('Erreur like AJAX :', error);
            });
        });
    });
});