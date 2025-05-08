import { useState, useEffect } from "react";
import "./Style/CardListDestinataire.css";

const CardListDestinataire = ({ setMail }) => {
  const [destinataires, setDestinataires] = useState([]);
  const [error, setError] = useState("");

  useEffect(() => {
    const ListDestinataire = async () => {
      try {
        const response = await fetch("http://localhost:8000/mail/ListDestinataireClient/1");
        const data = await response.json();

        if (response.ok && data.success) {
          setDestinataires(data.data);
        } else {
          throw new Error("Erreur lors de la récupération");
        }
      } catch (e) {
        console.error("Erreur lors de la récupération des destinataires :", e);
        setError("Une erreur s'est produite. Veuillez réessayer.");
      }
    };
    ListDestinataire();
  }, []);

  return (
    <div className="card-list-container">
      {error && <p style={{ color: "red" }}>{error}</p>}
      <table className="destinataires-table">
        <thead>
          <tr>
            <th>Mail destinataire</th>
            <th>Sélectionner</th>
          </tr>
        </thead>
        <tbody>
          {destinataires.map((dest) => (
            <tr key={dest.id}>
              <td>{dest.mail}</td>
              <td>
                <input
                  type="checkbox"
                  onChange={(e) => {
                    if (e.target.checked) {
                      setMail((prev) => ({
                        ...prev,
                        to: [...prev.to, dest.mail],
                        toListId: [...(prev.toListId || []), dest.id],
                      }));
                    } else {
                      setMail((prev) => ({
                        ...prev,
                        to: prev.to.filter((m) => m !== dest.mail),
                        toListId: (prev.toListId || []).filter((id) => id !== dest.id),
                      }));
                    }
                  }}
                />
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
};

export default CardListDestinataire;
