/**
 * @property HTMLFormElement formElement
 * @property HTMLInputElement inputElement
 * @property HTMLButtonElement inputElement
 */
class FormSearch {
    constructor(config = {
        formName: "search-repositories",
        queryName: "query",
        delay: 800,
        pageSize: 10,
        resultID: "results",
        templateItemID: "template-item"
    }) {
        let formElement = document.forms.namedItem(config.formName);

        if (formElement.length === null) {
            throw new Error("Form not found with this name: " + config.formName);
        }

        this.formElement = formElement;
        this.inputElement = this.formElement.elements.namedItem(config.queryName);
        this.submitElement = this.formElement.querySelector("button[type=submit]");
        this.keyupTimeout = null;

        this.inputElement.addEventListener("keyup", () => {
            clearTimeout(this.keyupTimeout);
            this.keyupTimeout = setTimeout(() => {
                this.toogleSubmit();
                this.lookRepositories();
            }, config.delay);
        });

        this.formElement.addEventListener("submit", event => {
            event.preventDefault();
            this.lookRepositories();

            return false;
        });

        this.page = 1;
        this.lastPage = 1;
        this.pageSize = config.pageSize;

        if (this.pageSize > 100 || this.pageSize < 10) {
            throw new Error("pageSize can't be more than 100 or lower than 10");
        }

        this.request = new XMLHttpRequest();

        let resultsElement = document.getElementById(config.resultID);

        if (resultsElement === null) {
            throw new Error("Can't find resultElement, check the ID");
        }

        this.emptyElement = resultsElement.querySelector(".empty");
        this.itemsElement = resultsElement.querySelector(".items");
        this.progressElement = resultsElement.querySelector(".progress");

        if (this.emptyElement === null || this.itemsElement === null || this.progressElement === null) {
            throw new Error("Can't find one of container, check if you give proper classes: 'empty', 'items', 'progress'");
        }

        let templateItemElement = document.getElementById(config.templateItemID);
        if (templateItemElement === null) {
            throw  new Error("Can't find the template");
        }

        this.templateItemElement = document.createElement("div");
        this.templateItemElement.innerHTML = templateItemElement.innerHTML;
        templateItemElement.remove();
    }

    toogleSubmit() {
        const value = this.inputElement.value;

        if (value === null || value === "") {
            this.submitElement.setAttribute("disabled", "disabled");
        } else {
            this.submitElement.removeAttribute("disabled");
        }
    }

    buildUrl() {
        const query = this.inputElement.value;

        let url = this.formElement.action + "?";
        url += "query="+ query + "&page=" + this.page + "&pageSize=" + this.pageSize;

        return encodeURI(url);
    }

    lookRepositories() {
        this.progressElement.classList.remove("hidden");
        this.emptyElement.classList.add("hidden");

        const url = this.buildUrl();
        this.request.open(this.formElement.method, url);
        this.request.addEventListener("readystatechange", () => {
            if (this.request.status === 200 && this.request.readyState === 4) {
                const response = JSON.parse(this.request.responseText);

                if (response.items.length === 0) {
                    this.emptyElement.classList.remove("hidden");
                } else {
                    this.itemsElement.innerHTML = "";
                    this.fillWithItems(response.items);
                }

                this.lastPage = response.lastPage;
                this.progressElement.classList.add("hidden");
            }
        });
        this.request.send();
    }

    fillWithItems(items) {
        for (const item of items) {
            let itemElement = this.templateItemElement.cloneNode(true);

            for (const key in item) {
                const attributeKey = "data-" + key;

                let element = itemElement.querySelector("["+ attributeKey +"]");

                if (element !== null) {
                    const attributeValue = element.getAttribute(attributeKey);

                    if (attributeValue == "" || attributeValue == null) {
                        element.innerHTML = item[key];
                    } else {
                        element.setAttribute(attributeValue, item[key]);
                    }
                }
            }

            for (const child of itemElement.children) {
                this.itemsElement.appendChild(child);
            }
        }
    }
}