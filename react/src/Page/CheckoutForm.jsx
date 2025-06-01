import { useEffect, useState } from "react";
import { useStripe, useElements, CardElement } from "@stripe/react-stripe-js";
import { toast } from "react-toastify";
import { useStateContext } from "../Context/ContextProvider";
import "./Style/StripeCard.css";
import axiosClient from "../axios-client";

const CheckoutForm = ({ price, nom }) => {
  const stripe = useStripe();
  const elements = useElements();
  const { user } = useStateContext();

  const [email, setEmail] = useState("");
  const [name, setName] = useState("");
  const [amount, setAmount] = useState(null); // Montant en centimes
  const [processing, setProcessing] = useState(false);

  useEffect(() => {
    if (price) {
      const prixParsed = parseFloat(typeof price === "string" ? price.replace(",", ".") : price);
      if (!isNaN(prixParsed)) {
        setAmount(Math.round(prixParsed * 100)); // conversion euros → centimes
      }
    }
  }, [price]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!stripe || !elements || !amount) return;
    
    const IdUser = user.id;
    setProcessing(true);

     try {
      const response = await axiosClient.post(`stripe/create-payment-intent`, { amount, email ,nom , IdUser });
      const data = response.data;

      if (data.error) {
        toast.error(`Erreur backend : ${data.error}`);
        setProcessing(false);
        return;
      }

      const clientSecret = data.clientSecret;

      const result = await stripe.confirmCardPayment(clientSecret, {
        payment_method: {
          card: elements.getElement(CardElement),
          billing_details: {
            name,
            email,
          },
        },
      });

      if (result.error) {
        toast.error(`Erreur Stripe : ${result.error.message}`);
      } else if (result.paymentIntent && result.paymentIntent.status === "succeeded") {
        toast.success("Paiement réussi !");
      } else {
        toast.warning("Paiement non complété.");
      }
    } catch (err) {
      console.error("Erreur lors du traitement :", err);
      toast.error("Une erreur est survenue.");
    }

    setProcessing(false);
  };

  return (
    <form className="stripe-card" onSubmit={handleSubmit}>
      <h2>Paiement sécurisé</h2>
      <input
        type="text"
        placeholder="Nom complet"
        value={name}
        onChange={(e) => setName(e.target.value)}
        required
      />
      <input
        type="email"
        placeholder="Adresse email"
        value={email}
        onChange={(e) => setEmail(e.target.value)}
        required
      />
      <div className="card-element">
        <CardElement options={{ style: { base: { fontSize: "16px" } } }} />
      </div>
      <button type="submit" disabled={!stripe || processing}>
        {processing ? "Traitement..." : "Payer"}
      </button>
    </form>
  );
};

export default CheckoutForm;
