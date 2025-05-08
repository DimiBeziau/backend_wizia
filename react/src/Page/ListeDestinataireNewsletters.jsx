import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { toast } from "react-toastify";
import "./Style/ListeDestinataireNewsletters.css";

const ListeDestinataireNewsletters = () => {
  const navigate = useNavigate();
  const [destinataires, setDestinataires] = useState([]);
  const idUser = 1;


  useEffect(() => {
    const fetchDestinataires = async () => {
      try {
        const response = await fetch(`${process.env.VITE_API_BASE_URL}mail/ListDestinataireClient/${idUser}`);
        const data = await response.json();
        if (data.success) {
          setDestinataires(data.data);
        } else {
          toast.error("Erreur de récupération des destinataires");
        }
      } catch (error) {
        toast.error("Erreur lors du chargement");
        console.error("Erreur fetch :", error);
      }
    };

    fetchDestinataires();
  }, [idUser]);


  const handleDelete = async (id) => {
    if (!window.confirm("Confirmer la suppression ?")) return;

    try {
      const response = await fetch(`${process.env.VITE_API_BASE_URL}mail/ListDestinataireClient/${id}`, {
        method: "DELETE",
      });
      const data = await response.json();
      if (data.success) {
        setDestinataires((prev) => prev.filter((d) => d.id !== id));
        toast.success("Destinataire supprimé");
      } else {
        toast.error("Erreur de suppression : " + data.message);
      }
    } catch (error) {
      toast.error("Erreur serveur lors de la suppression");
      console.error("Erreur DELETE :", error);
    }
  };

  const handleEditAdd = (destinataire = null) => {
    navigate("/Dashboard/Newsletters/ListeDestinataireNewsletters/FormulaireDestinataire", {
      state: destinataire,
    });
  };

  const onClosed = () => {
    navigate("/Dashboard/Newsletters");
  };

  return (
    <div className="ListeDestinataireNewsletters">
      <h2>Liste des destinataires</h2>

      <div className="ListeButtons">
        <button onClick={() => handleEditAdd(null)}>➕ Ajouter un destinataire</button>
        <button onClick={onClosed}>❌ Fermer</button>
      </div>

      <br />

      <table>
        <thead>
          <tr>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Email</th>
            <th>Modifier</th>
            <th>Supprimer</th>
          </tr>
        </thead>
        <tbody>
          {destinataires.length === 0 ? (
            <tr>
              <td colSpan="5">Aucun destinataire trouvé.</td>
            </tr>
          ) : (
            destinataires.map((dest) => (
              <tr key={dest.id}>
                <td>{dest.nom}</td>
                <td>{dest.prenom}</td>
                <td>{dest.mail}</td>
                <td>
                  <button onClick={() => handleEditAdd(dest)}>✏️</button>
                </td>
                <td>
                  <button onClick={() => handleDelete(dest.id)}>🗑️</button>
                </td>
              </tr>
            ))
          )}
        </tbody>
      </table>
    </div>
  );
};

export default ListeDestinataireNewsletters;
