import {Controller} from "@hotwired/stimulus"

export default class extends Controller {
    static targets = ["input", "results", "codepostal"]

    connect() {
        this.abortController = null
    }

    search() {
        const nom = this.inputTarget.value.trim()

        // Ne lance la recherche qu'à partir de 2 caractères
        if (nom.length < 2) {
            this.resultsTarget.innerHTML = ""
            return
        }

        // Annule la requête précédente si elle existe
        if (this.abortController) {
            this.abortController.abort()
        }

        this.abortController = new AbortController()

        fetch(`https://geo.api.gouv.fr/communes?nom=${encodeURIComponent(nom)}&fields=departement&boost=population&limit=5`, {
            signal: this.abortController.signal
        })
            .then(response => {
                if (!response.ok) throw new Error("Erreur API")
                return response.json()
            })
            .then(data => {
                this.displayResults(data)
            })
            .catch(error => {
                if (error.name !== "AbortError") {
                    console.error("Erreur lors de l'appel à l'API Geo :", error)
                }
            })
    }

    displayResults(communes) {
        if (!communes.length) {
            this.resultsTarget.innerHTML = "<li>Aucune commune trouvée.</li>"
            return
        }

        this.resultsTarget.innerHTML = communes.map(commune => `
            <li data-action="click->commune#autocomplete" data-ville="${commune.nom}" data-cp="${commune.code??'N/A'}">
                ${commune.nom} (${commune.departement?.nom ?? "N/A"}, ${commune.code??"N/A"})
            </li>
        `).join("")
    }

    autocomplete(event){
        this.inputTarget.value = event.srcElement.dataset.ville;
        this.codepostalTarget.value = event.srcElement.dataset.cp;
    }
}
