import NavBar from "../Components/Retulisatble/NavBar";
import CardWelcome from "../Components/Retulisatble/CardWelcome";
const Abonnement = () => {
return(
<div className="Abonnement">
     <NavBar />
        <h1>Abonnement</h1>
        <div className="CardContainer">
         <CardWelcome 
          nom="Newsletters" 
          description="Envoyez des newsletters de votre activité à vos clients" 
          prix = "Free"
          icon="https://cdn-icons-png.flaticon.com/512/561/561127.png" 
          buttonText="Payé" 
          destination="Abonnement/UpdateAbonnement" 
        />
         <CardWelcome 
          nom="Newsletters" 
          description="Envoyez des newsletters de votre activité à vos clients" 
          prix = "17,99"
          icon="https://cdn-icons-png.flaticon.com/512/561/561127.png"  
          buttonText="Payé" 
          destination="Abonnement/UpdateAbonnement" 
        />
         <CardWelcome 
          nom="Newsletters" 
          description="Envoyez des newsletters de votre activité à vos clients" 
          icon="https://cdn-icons-png.flaticon.com/512/561/561127.png" 
          prix = "29,99"
          buttonText="Payé" 
          destination="Abonnement/UpdateAbonnement" 
            />
     </div>
</div>
)
};
export default Abonnement;