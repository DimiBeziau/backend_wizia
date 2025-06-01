
import React, { useState } from "react";
import "./Style/CreateCompte.css";
import { Navigate, useNavigate } from "react-router-dom";

const CreateCompte = () => {
  const [error, setError] = useState('');
  const navigate = useNavigate();
  const [formData, setFormData] = useState({
    name: "",
    firstName: "",
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
      
      const response = await fetch(`${import.meta.env.VITE_API_BASE_URL}auth/register`, {

        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json"
        },
        body: JSON.stringify(formData),
      });
      const data = await response.json();
      if (response.ok) {
        window.location.href = "/";
      } else {
        setError(data.message || "Erreur lors de la création du compte");
      }

    } catch (error) {
      console.error(error);
      setError("Erreur lors de la création du compte");
    }
  };

  return (
    <div className="CreateCompte">
      <button className="closeButton" onClick={onClosed}>❌</button>
      <form onSubmit={handleSubmit}>
        <h2>Créer un compte</h2>

        <label>Nom :</label>
        <input type="text" name="name" onChange={handleChange} required />

        <label>Prénom :</label>
        <input type="text" name="firstName" onChange={handleChange} required />

        <label>Email :</label>
        <input type="email" name="email" onChange={handleChange} required />

        <label>Mot de passe :</label>
        <input type="password" name="password" onChange={handleChange} required />

        <button type="submit">Créer le compte</button>
        {error && <p className="errorText">{error}</p>}
      </form>
    </div>
  );
};

export default CreateCompte;
