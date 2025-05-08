import { createBrowserRouter } from "react-router-dom";
import DefaultLayout from "../Components/DefaultLayout";
import GuestLayout from "../Components/GuestLayout";
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
import AddGoogle_Analytics from "../Page/AddGoogleAnalytics.jsx";
import Abonnement from "../Page/Abonnement.jsx";
import UpdateAbonnement from "../Page/UpdateAbonnement.jsx";
import History from "../Page/History.jsx";
import UpdateProfil from "../Page/UpdateProfil.jsx";
import MentionsLegales from "../Page/MentionsLegales.jsx";
import PolitiqueConfidentialite from "../Page/PolitiqueConfidentialite.jsx";
import MailPassword from "../Page/MailPassword.jsx";

const router = createBrowserRouter([
  {
    path: '/',
    element: <GuestLayout />,
    children:
      [
        {
          path: '/',
          element: <Connexion />
        }, {
          path: '/login',
          element: <Connexion />
        }, {
          path: "/CreateCompte",
          element: <CreateCompte />
        }, {
          path: '/PasswordForget',
          element: <PasswordForget />
        },
        {
          path: "/PasswordForget/MailPassword",
          element: <MailPassword />
        }
      ]
  },
  {
    path: '/',
    element: <DefaultLayout />,
    children: [
      {
        path: "/Dashboard",
        element: <Welcome />
      }, {
        path: "/Dashboard/Réseaux_Sociaux",
        element: <ReseauxSociaux />
      },
      {
        path: "/Dashboard/Newsletters",
        element: <Newsletters />
      }, {
        path: "/Dashboard/Newsletters/ListeDestinataireNewsletters",
        element: <ListeDestinataireNewsletters />
      }, {
        path: "/Dashboard/Newsletters/ListeDestinataireNewsletters/FormulaireDestinataire",
        element: <FormulaireDestinataire />
      }, {
        path: "/Dashboard/Google_Analytics",
        element: <Google_Analytics />
      }, {
        path: "/Dashboard/Google_Analytics/AddGoogle_Analytics",
        element: <AddGoogle_Analytics />
      }, {
        path: "/Dashboard/Abonnement",
        element: <Abonnement />
      }, {
        path: "/Dashboard/Abonnement/UpdateAbonnement",
        element: <UpdateAbonnement />
      }, {
        path: "/Dashboard/History",
        element: <History />
      }, {
        path: "/Dashboard/UpdateProfil",
        element: <UpdateProfil />
      }, {
        path: "/Test",
        element: <Test />
      }, {
        path: "/mentions-legales",
        element: <MentionsLegales />
      }, {
        path: "/politique-confidentialite",
        element: <PolitiqueConfidentialite />
      }, {
        path: "*",
        element: <Error />
      }
    ]
  }
])

export default router