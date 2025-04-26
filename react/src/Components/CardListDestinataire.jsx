import { useState } from "react";
import "./Style/CardListDestinataire.css"; // <-- j'importe le CSS

const CardListDestinataire = () => {
  const [destinataires, setDestinataires] = useState([
    { id: 1, mail: "jean.dupont@email.com" },
    { id: 2, mail: "claire.durand@email.com" },
  ]);

  return (
    <div className="card-list-container">
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
              <td><input type="checkbox" /></td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
};

export default CardListDestinataire;
