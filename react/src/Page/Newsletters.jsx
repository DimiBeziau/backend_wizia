import NavBar from "../Components/Retulisatble/NavBar";
import Marronniers from "../Components/Retulisatble/Marronniers";
import CardIA from "../Components/Retulisatble/CardIA";
import CardListDestinataire from "../Components/CardListDestinataire";
import { useState } from 'react';
import "./Style/Newletters.css";
import { toast } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';
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
    fromEmail: "wiz.ia@dimitribeziau.fr",
    fromName: "WIZIA@gmail.com",
    to: [],
    toListId: [],
    body: '',
    subject: "Ma newsletter",
    altBody: "Texte brut de la newsletter",
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
      if (generatedPrompt !== "" && selectedDates.startDate !== null && Mail.to.length > 0) {

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
              to: Mail.to,
              subject: Mail.subject,
              body: generatedPrompt,
              altBody: Mail.altBody,
              fromName: Mail.fromName,
              fromEmail: Mail.fromEmail,
            }),
          };

          const response = await fetch(`${import.meta.env.VITE_API_BASE_URL}mail/generateMail`, options);
          // const response = await fetch(`${import.meta.env.VITE_API_BASE_URL}mail/generateMail`, options);
          const data = await response.json();
          AddNewsletters();

          if (response.ok) {
            toast('Mail envoyé ', {
              type: "success"
            })
          } else {
            toast('Erreur lors de l\’envoi', {
              type: "error"
            })
          }
        } else {
          toast('La date sélectionnée n est pas aujourd hui !', {
            type: "error"
          })
        }
      } else {
        toast('Veuillez générer du contenu et choisir une date !', {
          type: "error"
        })
      }
    } catch (e) {
      console.error("Erreur réseau :", e);
      setError("Erreur réseau, impossible d'envoyer pour le moment.");
      toast('Erreur réseau, impossible d envoyer pour le moment.', {
        type: "error"
      })
    }
  };
  const AddNewsletters = async () => {
    const options = {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json; charset=utf-8',
      },

      body: JSON.stringify({
        to: Mail.to,
        toListId: Mail.toListId,
        subject: Mail.subject,
        body: generatedPrompt,
        altBody: Mail.altBody,
        fromName: Mail.fromName,
        fromEmail: Mail.fromEmail,
      }),
    };

    const response = await fetch(`${import.meta.env.VITE_API_BASE_URL}mail/AddMail/1`, options);
    const data = await response.json();
    if (data) {
      return true
    } else {
      return false
    }

  }

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
          <CardListDestinataire setMail={setMail} />
        </div>

      </div>
      <div>

        {generatedPrompt !== "" && selectedDates.startDate !== null && (
          <button onClick={ValiderNewsletters}>Valider la Newsletters</button>
        )}

      </div>
      {error && <p style={{ color: "red" }}>{error}</p>}
    </div>
  );
};

export default Newsletters;
