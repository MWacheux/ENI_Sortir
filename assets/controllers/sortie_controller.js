import {Controller} from "@hotwired/stimulus"

export default class extends Controller {
    static targets = ["dropdownLieu", "divNewLieu","dropdownVille", "divNewVille"]

    connect()  {
        this.toggleShow();
    }

    toggleShow(){
        if (0 === this.dropdownVilleTarget.selectedIndex){
            this.divNewVilleTarget.classList.remove("visually-hidden")
        } else {
            this.divNewVilleTarget.classList.add("visually-hidden")
        }
        if (0 === this.dropdownLieuTarget.selectedIndex){
            this.divNewLieuTarget.classList.remove("visually-hidden")
        } else {
            this.divNewLieuTarget.classList.add("visually-hidden")
        }
    }

}
