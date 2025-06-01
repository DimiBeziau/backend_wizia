import { useEffect, useState } from "react";
import NavBar from "../Components/Retulisatble/NavBar";
import CardWelcome from "../Components/Retulisatble/CardWelcome";
import { useStateContext } from "../Context/ContextProvider";

const Abonnement = () => {
  const [typeAbonnement, setTypeAbonnement] = useState(null);
  const { user } = useStateContext();

  useEffect(() => {
    const fetchAbonnement = async () => {
      try {
        if (!user?.id) return;

        const response = await fetch(
          `${import.meta.env.VITE_API_BASE_URL}stripe/abonnement/${user.id}`
        );
        if (!response.ok) {
          throw new Error("Erreur lors de la récupération de l’abonnement");
        }
        const data = await response.json();
        setTypeAbonnement(data);
      } catch (error) {
        console.error("Erreur lors de la récupération de l’abonnement :", error);
      }
    };

    fetchAbonnement();
  }, [user.id]);

  // Fonction qui retourne true pour griser la carte correspondant à l’abonnement actif
  const isGrayed = (type) => {
    if (typeAbonnement === "isFree" && type === "Free") return true;
    if (typeAbonnement === "isPremium" && type === "Premium") return true;
    if (typeAbonnement === "isProfessionnel" && type === "Professionnel") return true;
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
          buttonText={typeAbonnement === "isFree" ? "Actuel" : "S'abonner"}
          destination="Abonnement/UpdateAbonnement"
          gray={isGrayed("Free")}
        />
        <CardWelcome
          nom="Premium"
          description="Plus de fonctionnalités et d'options"
          prix="17,99"
          icon="https://cdn-icons-png.flaticon.com/512/561/561127.png"
          buttonText={typeAbonnement === "isPremium" ? "Actuel" : "S'abonner"}
          destination="Abonnement/UpdateAbonnement"
          gray={isGrayed("Premium")}
        />
        <CardWelcome
          nom="Professionnel"
          description="Accès complet à toutes les fonctions"
          prix="29,99"
          icon="https://cdn-icons-png.flaticon.com/512/561/561127.png"
          buttonText={typeAbonnement === "isProfessionnel" ? "Actuel" : "S'abonner"}
          destination="Abonnement/UpdateAbonnement"
          gray={isGrayed("Professionnel")}
        />
      </div>
    </div>
  );
};

export default Abonnement;
