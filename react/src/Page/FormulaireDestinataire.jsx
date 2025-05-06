import React, { useState } from "react";
import { useLocation, useNavigate } from "react-router-dom";
import { toast } from "react-toastify"; 
import "react-toastify/dist/ReactToastify.css";
import "./Style/FormulaireDestinataire.css";

const FormulaireDestinataire = () => {
  const location = useLocation();
  const navigate = useNavigate();
  const destinataire = location.state || { nom: "", prenom: "", mail: "", idUser: null };

  const [formData, setFormData] = useState({
    id: destinataire.id || null,
    nom: destinataire.nom,
    prenom: destinataire.prenom,
    mail: destinataire.mail,
  });

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    try {
      let response;
      const idUser =  1; 

      if (destinataire?.id) {
        // Mise à jour
        response = await fetch(`https://api.wizia.dimitribeziau.fr/mail/UpdateDestinataireClient/${idUser}`, {
          method: "PUT",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(formData),
        });
      } else {
        // Ajout
        response = await fetch(`https://api.wizia.dimitribeziau.fr/mail/AddDestinataireClient/${idUser}`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(formData),
        });
      }

      const data = await response.json();
      if (data.success) {
        toast.success(destinataire?.id ? "Modification réussie" : "Ajout réussi");
        navigate("/Dashboard/Newsletters/ListeDestinataireNewsletters");
      } else {
        toast.error("Erreur : " + data.message);
        console.error("Erreur API :", data.error);
      }
    } catch (error) {
      console.error("Erreur requête :", error);
      toast.error("Une erreur est survenue.");
    }
  };

  return (
    <div className="FormulaireDestinataire">
      <h2>{destinataire?.id ? "Modifier le destinataire" : "Ajouter un destinataire"}</h2>
      <form onSubmit={handleSubmit}>
        <label>
          Nom :
          <input type="text" name="nom" value={formData.nom} onChange={handleChange} required />
        </label>
        <label>
          Prénom :
          <input type="text" name="prenom" value={formData.prenom} onChange={handleChange} required />
        </label>
        <label>
          Email :
          <input type="email" name="mail" value={formData.mail} onChange={handleChange} required />
        </label>
        <button type="submit">Enregistrer</button>
        <button type="button" onClick={() => navigate("/Dashboard/Newsletters/ListeDestinataireNewsletters")}>
          ❌ Annuler
        </button>
      </form>
    </div>
  );
};

export default FormulaireDestinataire;
