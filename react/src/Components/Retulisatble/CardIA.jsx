import { useState } from "react";
import './Style/CardIA.css';
import { toast } from 'react-toastify';

const CardIA = ({ prompt, Titre, onPromptGenerated }) => {
  const [Prompt, setPrompt] = useState("");
  const [error, setError] = useState("");
  //https://api.wizia.dimitribeziau.fr/ia/generateIA
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

      const reponse = await fetch('https://api.wizia.dimitribeziau.fr/ia/generateIA', Option);

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

  const publishPost = async () => {
    const response = await fetch("https://api.wizia.dimitribeziau.fr/post", {
      method: "POST",
      headers: {
        "Accept": "application/json",
        "Content-Type": "application/json"
      },
      body: JSON.stringify({ post: prompt })
    })

    const json = response.json();

    if (response.status === 200) {
      toast('Post correctement publié', {
        type: "success"
      })
    } else {
      toast('Erreur lors de la publication', {
        type: "error"
      })
    }
  }

  return (
    <div className="CardIA">
      <h2>{Titre}</h2>
      <button onClick={GenererMailType}>Générer</button>
      {Prompt !== "" && <button onClick={publishPost}>Publier</button>}
      <p>{Prompt}</p>
      {error && <p style={{ color: 'red' }}>{error}</p>}
    </div>
  );
};

export default CardIA;
