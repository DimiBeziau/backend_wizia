import { useState } from "react";
import './Style/CardIA.css';
import { toast } from 'react-toastify';

const CardIA = ({ prompt, Titre, onPromptGenerated }) => {
  const [Prompt, setPrompt] = useState("");
  const [error, setError] = useState("");

  const GenererMailType = async () => {
    try {
      // setPrompt(prompt); // test

      const Option = {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json; charset=utf-8',
        },
        body: JSON.stringify({
          prompt: prompt,
        }),
      };

      const reponse = await fetch(`${import.meta.env.VITE_API_BASE_URL}ia/generateIA`, Option);

      if (reponse.ok) {
        const reponseData = await reponse.json();

        setPrompt(reponseData.text);
        onPromptGenerated(reponseData.text);

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
      {Prompt !== "" && (
        <textarea
          style={{ width: "100%", height: "150px", borderRadius: "6px", backgroundColor: "#ffffff" }}
          value={Prompt}
          onChange={(event) => { setPrompt(event.target.value); onPromptGenerated(event.target.value) }}
        />
      )}
      <button onClick={GenererMailType}>Générer</button>

      {error && <p style={{ color: 'red' }}>{error}</p>}
    </div>
  );
};

export default CardIA;