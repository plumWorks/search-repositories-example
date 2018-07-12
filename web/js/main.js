let searchGithub;

function init() {
    searchGithub = new FormSearch();
}

document.addEventListener('readystatechange', event => {
    if (event.target.readyState === "interactive") {
        init();
    }
});