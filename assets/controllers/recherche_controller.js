import { Controller } from "@hotwired/stimulus";

/*
 * Champ de recherche : focus au chargement, curseur placé après le texte déjà saisi.
 */
export default class extends Controller {
  static targets = ["champ"];

  connect() {
    const champ = this.champTarget;
    champ.focus();
    const fin = champ.value.length;
    champ.setSelectionRange(fin, fin);
  }
}
