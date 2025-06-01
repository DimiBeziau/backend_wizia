import React, { useState } from "react";
import { useLocation, useNavigate } from "react-router-dom";
import { toast } from "react-toastify";
import "react-toastify/dist/ReactToastify.css";
import "./Style/FormulaireDestinataire.css";
import { useStateContext } from "../Context/ContextProvider";
import axiosClient from "../axios-client";

const FormulaireDestinataire = () => {
  const location = useLocation();
  const navigate = useNavigate();
  const destinataire = location.state || { nom: "", prenom: "", mail: "", idUser: null };
  const { user } = useStateContext();
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

   // try {
      if (destinataire?.id) {
        // Mise à jour
        await axiosClient.put(`mail/UpdateDestinataireClient/${user.id}`, formData);
      } else {
        // Ajout
        await axiosClient.post(`mail/AddDestinataireClient/${user.id}`, formData);
      }
      toast.success(destinataire?.id ? "Modification réussie" : "Ajout réussi");
      navigate("/Dashboard/Newsletters/ListeDestinataireNewsletters");
    // } catch (error) {
    //   console.error("Erreur requête :", error);
    //   toast.error("Une erreur est survenue.");
    // }
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
