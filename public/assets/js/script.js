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