import "./Style/cardWelcome.css";

import { useState } from 'react';
import { useNavigate } from "react-router-dom";

const CardWelcome = ({ nom, description,prix, icon, buttonText, destination }) => {
  const navigate = useNavigate();
  const [error, setError] = useState(""); 

  const ClickCard = () => {
    try {
      navigate(`/Dashboard/${destination}`, {
      state: { prix } 
    });
    } catch (e) {
      console.error('Erreur lors de la navigation :', e);
      setError("Une erreur s'est produite. Veuillez réessayer.");
    }
  };

  return (
    <div className="CardWelcome">
      {icon && <img src={icon} alt="icon" className="CardIcon" />}
      <h2>{nom}</h2>
      <a>{prix}</a>
      <p>{description}</p>
      <button onClick={ClickCard}>{buttonText}</button>
      {error !== "" && <p  style={{ color: 'red', textDecoration: 'none' }}>{error}</p>}
    </div>
  );
};

export default CardWelcome;
