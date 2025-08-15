import NavBar from "../Components/Retulisatble/NavBar";
import Marronniers from "../Components/Retulisatble/Marronniers";
import CardIA from "../Components/Retulisatble/CardIA";
import CardListDestinataire from "../Components/CardListDestinataire";
import { useState } from 'react';
import "./Style/Newletters.css";
import { toast } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';
import { useNavigate } from "react-router-dom";
import { useStateContext } from "../Context/ContextProvider";
import axiosClient from "../axios-client";

const Newsletters = () => {
  const [error, setError] = useState("");
  const [generatedPrompt, setGeneratedPrompt] = useState("");
  const [selectedDates, setSelectedDates] = useState({ startDate: null });
  const navigate = useNavigate();
  const { user } = useStateContext();
  console.log(user);
  const [users] = useState({
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
    attachment: "C:\Users\Matthieu\Pictures\Screenshots\Capture d'écran 2025-02-03 180734.png"
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
          const response = await axiosClient.post('mail/generateMail', {
            to: Mail.to,
            subject: Mail.subject,
            body: generatedPrompt,
            altBody: Mail.altBody,
            fromName: Mail.fromName,
            fromEmail: Mail.fromEmail
           
          });

        if (response.data.success) {
          await AddNewsletters();
          toast('Mail envoyé ', {
            type: "success"
          });
        } else {
          toast('Erreur lors de l\'envoi', {
            type: "error"
          });
        }
      } else {
        toast('La date sélectionnée n est pas aujourd hui !', {
          type: "error"
        });
      }
    } else {
      toast('Veuillez générer du contenu et choisir une date !', {
        type: "error"
      });
    }
    } catch (e) {
      console.error("Erreur réseau :", e);
      setError("Erreur réseau, impossible d'envoyer pour le moment.");
      toast('Erreur réseau, impossible d envoyer pour le moment.', {
        type: "error"
      });
    }
  };

  const AddNewsletters = async () => {
    try {
      const { data } = await axiosClient.post(`mail/AddMail/${user.id}`, {
        to: Mail.to,
        toListId: Mail.toListId,
        subject: Mail.subject,
        body: generatedPrompt,
        altBody: Mail.altBody,
        fromName: Mail.fromName,
        fromEmail: Mail.fromEmail,
      });
      return data.success;
    } catch (error) {
      console.error("Erreur lors de l'ajout de la newsletter :", error);
      return false;
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
