// PaiementStripe.jsx
import { loadStripe } from "@stripe/stripe-js";
import { Elements } from "@stripe/react-stripe-js";
import CheckoutForm from "./CheckoutForm";
import NavBar from "../Components/Retulisatble/NavBar";
import { ToastContainer } from "react-toastify";
import "react-toastify/dist/ReactToastify.css";
import { useLocation, useNavigate } from "react-router-dom";
const PaiementStripe = () => {

const stripePromise = loadStripe("pk_test_51RKyjqD0HuVWNNfnjN51P4NNwHAvF0kYBHKLLgpSgLfLdG1Vm2Etp24hbxTbW699vyzVBq7ZAbg16m1g1nm9uL9m00JPJkgBN4");
  const location = useLocation();
  const navigate = useNavigate();
  const ValeurPrix = location.state || { prix: null };
  return (
  <>
    <NavBar />
    <button type="button" onClick={() => navigate("/Dashboard/Abonnement")}>
      Annuler
    </button>
    <div className="UpdateAbonnement">
      <Elements stripe={stripePromise}>
        <CheckoutForm price={ValeurPrix.prix} />
      </Elements>
      <ToastContainer position="top-right" />
    </div>
  </>
);
};

export default PaiementStripe;
