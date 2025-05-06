import NavBar from "../Components/Retulisatble/NavBar";
import CardWelcome from "../Components/Retulisatble/CardWelcome";

const Welcome = () => {
  return (
    <div>
      <NavBar />
      <h1>Home Page</h1>

     
      <div className="CardContainer">
        <CardWelcome 
          nom="Newsletters" 
          description="Envoyez des newsletters de votre activité à vos clients" 
          icon="https://cdn-icons-png.flaticon.com/512/561/561127.png" 
          prix=""
          buttonText="Lire" 
          destination="Newsletters" 
        />
        <CardWelcome 
          nom="Réseaux Sociaux" 
          description="Générez un post sur les plateformes sociales." 
          prix=""
          icon="https://cdn-icons-png.flaticon.com/512/2111/2111398.png" 
          buttonText="Lire" 
          destination="Réseaux_Sociaux" 
        />
        <CardWelcome 
          nom="Google my business" 
          description="Analysez le comportement de vos visiteurs et optimisez votre site web grâce aux données collectées par Google Analytics." 
          prix=""
          icon="https://www.gstatic.com/analytics-suite/header/suite/v2/ic_analytics.svg" 
          buttonText="Découvrir" 
          destination="Google_Analytics" 
/>
      </div>
    </div>
  );
};

export default Welcome;
