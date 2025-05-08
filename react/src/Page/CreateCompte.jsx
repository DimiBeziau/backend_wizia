
import React, { useState } from "react";
import "./Style/CreateCompte.css";
import { useNavigate } from "react-router-dom";

const CreateCompte = () => {
  const [error, setError] = useState('');
  const navigate = useNavigate();
  const [formData, setFormData] = useState({
    nom: "",
    prenom: "",
    email: "",
    password: "",
  });

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };
  const onClosed = () => {
    navigate("/");
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    try {
      const response = await fetch(`${process.env.VITE_API_BASE_URL}auth/register`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(formData),
      });
      if (response.ok) {
        const data = await response.json();
        console.log("Compte créé avec succès :", data);

      } else {
        setError("Erreur lors de la création du compte");
      }

    } catch (error) {
      console.error("Erreur lors de la création du compte :", error);
      setError("Erreur lors de la création du compte");
    }
  };

  return (
    <div className="CreateCompte">
      <button className="closeButton" onClick={onClosed}>❌</button>
      <form onSubmit={handleSubmit}>
        <h2>Créer un compte</h2>

        <label>Nom :</label>
        <input type="text" name="nom" value={formData.nom} onChange={handleChange} required />

        <label>Prénom :</label>
        <input type="text" name="prenom" value={formData.prenom} onChange={handleChange} required />

        <label>Email :</label>
        <input type="email" name="email" value={formData.email} onChange={handleChange} required />

        <label>Mot de passe :</label>
        <input type="password" name="password" value={formData.password} onChange={handleChange} required />

        <button type="submit">Créer le compte</button>
        {error && <p className="errorText">{error}</p>}
      </form>
    </div>
  );
};

export default CreateCompte;
