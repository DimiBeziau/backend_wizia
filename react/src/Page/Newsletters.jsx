import NavBar from "../Components/Retulisatble/NavBar";
import Marronniers from "../Components/Retulisatble/Marronniers";
import CardIA from "../Components/Retulisatble/CardIA";
import CardListDestinataire from "../Components/CardListDestinataire";
import { useState } from 'react';
import "./Style/Newletters.css"; 
import { useNavigate } from "react-router-dom";

const Newsletters = () => {
  const [error, setError] = useState("");
  const [generatedPrompt, setGeneratedPrompt] = useState("");
  const [selectedDates, setSelectedDates] = useState({ startDate: null });
  const navigate = useNavigate();

  const [user] = useState({
    userEmail: '',
    userPassWord: '',
    userAbonnement: '',
    userTravail: '',
  });

  const [Mail, setMail] = useState({
    fromEmail: user.userEmail,
    fromName: '',
    to: [''],
    body: '',
    subject: '',
    altBody: '',
    image: '',
    Date: '',
    Batch: false,
  });

  const AbonnementUser = async () => {
    // Logique à ajouter
  };

  const ListDestinataire = async () => {
    try {
      navigate('/Dashboard/Newsletters/ListeDestinataireNewsletters');
    } catch (e) {
      console.error('Erreur lors de la navigation :', e);
      setError("Une erreur s'est produite. Veuillez réessayer.");
    }
  };

  const formatDateAmerican = (date) => {
    const year = date.getFullYear();
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    const day = date.getDate().toString().padStart(2, '0');
    return `${year}${month}${day}`; 
  };

  const ValiderNewsletters = async () => {
    try {
      if (generatedPrompt !== "" && selectedDates.startDate !== null) {
        const today = new Date();
        const formattedToday = formatDateAmerican(today);
        const formattedSelectedDate = formatDateAmerican(new Date(selectedDates.startDate));

        if (formattedSelectedDate === formattedToday) {
          const options = {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json; charset=utf-8',
            },
            body: JSON.stringify({
              to: "querelmatthieu@gmail.com",
              subject: "Ma newsletter",
              body: generatedPrompt,
              altBody: "Texte brut de la newsletter",
              fromName: "Mon entreprise",
              fromEmail: "contact@dimitribeziau.fr",
            }),
          };

          const response = await fetch('https://api.wizia.dimitribeziau.fr/mail/generateMail', options);
          const data = await response.json();

          if (response.ok) {
            console.log("Mail envoyé :", data);
          } else {
            console.error("Erreur lors de l’envoi :", data);
          }
        } else {
          console.log("La date sélectionnée n'est pas aujourd'hui !");
        }
      } else {
        setError("Veuillez générer du contenu et choisir une date !");
      }
    } catch (e) {
      console.error("Erreur réseau :", e);
      setError("Erreur réseau, impossible d'envoyer pour le moment.");
    }
  };

  return (
    <div className="Newsletters">
      <NavBar />
      <div className="NewslettersHeader">
        <h1>Newsletters</h1>
        <button onClick={ListDestinataire}>Liste destinataire</button>
      </div>
      <div className="NewslettersContent">
        <div className="NewslettersIA">
          <CardIA
            prompt="le prompte est superregeeeeeeeeeeeeeeeeeeeeeeeee"
            Titre="Contenu de la Newsletters"
            onPromptGenerated={setGeneratedPrompt}
          />
        </div>

        <div className="NewslettersBord">
          <Marronniers onDateChange={setSelectedDates} />
        </div>
        <div className="NewslettersListDestinataire">
          <CardListDestinataire />
        </div>

      </div>
      <div>
        <button onClick={ValiderNewsletters}>Valider la Newsletters</button>
      </div>
      {error && <p style={{ color: "red" }}>{error}</p>}
    </div>
  );
};

export default Newsletters;
