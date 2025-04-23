import { useState } from "react";
import './Style/CardIA.css';

const CardIA = ({ prompt , Titre ,onPromptGenerated }) => {
  const [Prompt, setPrompt] = useState("");
  const [error, setError] = useState("");

  const GenererMailType = async () => {
      try {
          // setPrompt("mon prompte bfuvyqvysduysdguygfsydfytsg"); // test
          // onPromptGenerated("mon prompte bfuvyqvysduysdguygfsydfytsg");//test
      const Option = {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json; charset=utf-8',
        },
        body: JSON.stringify({
          message: prompt,
        }),
      };

      const reponse = await fetch('https://api.wizia.dimitribeziau.fr/ia/generateIA', Option);

      if (reponse.ok) {
        const reponseData = await reponse.json();
                setPrompt(reponseData.resultat); 
          onPromptGenerated(reponseData.resultat);
          
      } else {
        throw new Error("Réponse non OK");
      }
    } catch (e) {
      setError("Impossible de générer le prompt");
      console.error(e);
    }
  };

  return (
    <div className="CardIA">
          <h2>{Titre}</h2>
      <button onClick={GenererMailType}>Générer</button>
      <p>{Prompt}</p>
      {error && <p style={{ color: 'red' }}>{error}</p>}
    </div>
  );
};

export default CardIA;
