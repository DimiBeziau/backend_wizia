import { useEffect, useState } from "react";
import NavBar from "../Components/Retulisatble/NavBar";
import CardWelcome from "../Components/Retulisatble/CardWelcome";

const Abonnement = () => {
  const [typeAbonnement, setTypeAbonnement] = useState(null);
  const [userId, setUserId] = useState(1); 

  useEffect(() => {
    const fetchAbonnement = async () => {
      try {
        const response = await fetch(`${process.env.VITE_API_BASE_URL}/stripe/abonnement/${userId}`);
        const data = await response.json();
        setTypeAbonnement(data); 
      } catch (error) {
        console.error("Erreur lors de la récupération de l’abonnement :", error);
      }
    };

    fetchAbonnement();
  }, [userId]);

  const isGrayed = (type) => {
    if (typeAbonnement === "isFree" && type !== "Free") return true;
    if (typeAbonnement === "isPremium" && type === "Professionnel") return true;
    return false;
  };

  return (
    <div className="Abonnement">
      <NavBar />
      <h1>Abonnement</h1>
      <div className="CardContainer">
        <CardWelcome
          nom="Free"
          description="Envoyez des newsletters gratuitement"
          prix="Free"
          icon="https://cdn-icons-png.flaticon.com/512/561/561127.png"
          buttonText="Actuel"
          destination="Abonnement/UpdateAbonnement"
          gray={isGrayed("Free")}
        />
        <CardWelcome
          nom="Premium"
          description="Plus de fonctionnalités sociales"
          prix="17,99"
          icon="https://cdn-icons-png.flaticon.com/512/561/561127.png"
          buttonText="Payé"
          destination="Abonnement/UpdateAbonnement"
          gray={isGrayed("Premium")}
        />
        <CardWelcome
          nom="Professionnel"
          description="Accès complet à toutes les fonctions"
          prix="29,99"
          icon="https://cdn-icons-png.flaticon.com/512/561/561127.png"
          buttonText="Payé"
          destination="Abonnement/UpdateAbonnement"
          gray={isGrayed("Professionnel")}
        />
      </div>
    </div>
  );
};

export default Abonnement;
