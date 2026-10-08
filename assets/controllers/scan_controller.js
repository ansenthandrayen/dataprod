import { Controller } from "@hotwired/stimulus";

/*
 * Contrôle de chaque scan sur la page d'intégration.
 * Le serveur reste l'autorité : ce contrôle est un confort, la validation finale re-contrôle tout.
 */
export default class extends Controller {
  static values = { url: String };

  // Touche Entrée (envoyée par le scanner en fin de lecture) : on contrôle, on n'envoie pas le formulaire
  touche(event) {
    const champ = event.target;
    if (event.key !== "Enter" || !this.estChampDeScan(champ)) return;
    event.preventDefault();
    this.verifier(champ);
  }

  // Saisie à la main, ou scanner qui termine par Tab : le champ perd le focus après modification
  changement(event) {
    if (this.estChampDeScan(event.target)) this.verifier(event.target);
  }

  // Dès qu'un champ est modifié, son ancien verdict ne vaut plus
  saisie(event) {
    const champ = event.target;
    if (!this.estChampDeScan(champ)) return;
    delete champ.dataset.etat;
    delete champ.dataset.verifie;
    champ.removeAttribute("aria-invalid");
    this.messageDe(champ).textContent = "";
  }

  async verifier(champ) {
    const sn = champ.value.trim();
    // Rien à contrôler, déjà contrôlé, ou contrôle en cours
    if (sn === "" || champ.dataset.verifie === sn || champ.dataset.encours)
      return;

    champ.dataset.encours = "1";
    champ.readOnly = true; // évite de mélanger les caractères du scan suivant avec le contrôle en cours

    const params = new URLSearchParams({ type: champ.dataset.scanType, sn });
    // Les SN déjà acceptés dans les AUTRES champs (pour détecter un doublon dans le formulaire)
    this.dejaAcceptes(champ).forEach((autre) => params.append("deja[]", autre));

    try {
      const reponse = await fetch(`${this.urlValue}?${params}`, {
        headers: { Accept: "application/json" },
        credentials: "same-origin",
      });
      const donnees = await reponse.json();

      if (donnees.ok) {
        champ.dataset.etat = "ok";
        champ.dataset.verifie = sn;
        this.messageDe(champ).textContent = "";
        this.passerAuSuivant(champ);
      } else {
        // Scan refusé : message, champ vidé, on reste dessus pour rescanner
        champ.value = "";
        champ.setAttribute("aria-invalid", "true");
        this.messageDe(champ).textContent = donnees.erreur;
        champ.focus();
      }
    } catch (erreur) {
      // Réseau coupé ou session expirée : on ne bloque pas, le serveur vérifiera à la validation
      this.messageDe(champ).textContent =
        "Contrôle immédiat indisponible : le serveur vérifiera à la validation.";
      champ.dataset.verifie = sn;
      this.passerAuSuivant(champ);
    } finally {
      champ.readOnly = false;
      delete champ.dataset.encours;
    }
  }

  estChampDeScan(element) {
    return element instanceof HTMLInputElement && "scanType" in element.dataset;
  }

  champs() {
    return [...this.element.querySelectorAll("input[data-scan-type]")];
  }

  dejaAcceptes(champ) {
    return this.champs()
      .filter(
        (autre) =>
          autre !== champ &&
          autre.dataset.scanType === "sous_ensemble" &&
          autre.dataset.etat === "ok",
      )
      .map((autre) => autre.value.trim());
  }

  passerAuSuivant(champ) {
    const champs = this.champs();
    const suivant = champs[champs.indexOf(champ) + 1];
    // Dernier champ : le focus va sur le bouton, Entrée lance alors la validation
    (suivant ?? this.element.querySelector('button[type="submit"]'))?.focus();
  }

  messageDe(champ) {
    let message = champ.parentElement.querySelector(".erreur-scan");
    if (!message) {
      message = document.createElement("small");
      message.className = "erreur-scan";
      message.setAttribute("role", "alert");
      champ.insertAdjacentElement("afterend", message);
    }

    return message;
  }
}
