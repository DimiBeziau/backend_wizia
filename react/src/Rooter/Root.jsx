import { BrowserRouter, Routes, Route } from "react-router-dom";
import Welcome from "../Page/Welcome.jsx";
import Error from "../Page/Error.jsx";
import Test from "../Page/Test.jsx";
import Connexion from "../Page/Connexion.jsx";
import CreateCompte from "../Page/CreateCompte.jsx";
import PasswordForget from "../Page/PasswordForget.jsx";
import ReseauxSociaux from "../Page/ReseauxSociaux.jsx";
import Newsletters from "../Page/Newsletters.jsx";
import ListeDestinataireNewsletters from "../Page/ListeDestinataireNewsletters.jsx";
import FormulaireDestinataire from "../Page/FormulaireDestinataire.jsx";
import Google_Analytics from "../Page/Google_Analytics.jsx";
import AddGoogleAnalytics from "../Page/AddGoogleAnalytics.jsx";
import Abonnement from "../Page/Abonnement.jsx";
import UpdateAbonnement from "../Page/UpdateAbonnement.jsx";
import History from "../Page/History.jsx";
import UpdateProfil from "../Page/UpdateProfil.jsx";
import MentionsLegales from "../Page/MentionsLegales.jsx";
import PolitiqueConfidentialite from "../Page/PolitiqueConfidentialite.jsx";
import MailPassword from "../Page/MailPassword.jsx";


const Root = () => {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<Connexion />} />
        <Route path="/CreateCompte" element={<CreateCompte />} />
        <Route path="/PasswordForget" element={<PasswordForget />} />
        <Route path="/PasswordForget/MailPassword" element={<MailPassword />} />
        <Route path="/Dashboard" element={<Welcome />} />
        <Route path="/Dashboard/Réseaux_Sociaux" element={<ReseauxSociaux />} />
        <Route path="/Dashboard/Newsletters" element={<Newsletters />} />
        <Route path="/Dashboard/Newsletters/ListeDestinataireNewsletters" element={<ListeDestinataireNewsletters />} />
        <Route path="/Dashboard/Newsletters/ListeDestinataireNewsletters/FormulaireDestinataire" element={<FormulaireDestinataire />} />
        <Route path="/Dashboard/Google_Analytics" element={<Google_Analytics />} />
        <Route path="/Dashboard/Google_Analytics/AddGoogle_Analytics" element={<AddGoogleAnalytics />} />
        <Route path="/Dashboard/Abonnement" element={<Abonnement />} />
        <Route path="/Dashboard/Abonnement/UpdateAbonnement" element={<UpdateAbonnement />} />
        <Route path="/Dashboard/History" element={<History />} />
        <Route path="/Dashboard/UpdateProfil" element={<UpdateProfil />} />
        <Route path="/Test" element={<Test />} />
        <Route path="/mentions-legales" element={<MentionsLegales />} />
        <Route path="/politique-confidentialite" element={<PolitiqueConfidentialite />} />
        <Route path="*" element={<Error />} />
      </Routes>
    </BrowserRouter>
  );
};

export default Root;
