import React, { useState } from "react";
import { useNavigate } from "react-router-dom";
import "./Style/AddGoogleAnalytics.css";

const AddGoogleAnalytics = () => {
  const navigate = useNavigate();
  const [formData, setFormData] = useState({
    identifiantsAPI: "",
    PlacesAPI: "",
    
  });
const onClosed = () => {
    navigate("/Dashboard/Google_Analytics");
  };
  const handleChange = (e) => {
      const { name, value } = e.target
      setFormData((prev) => ({...prev,[name]:value}))
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    console.log("Infos client GA :", formData);
    
    navigate("/Dashboard/Google_Analytics");
  };

  return (
  <div className="AddGoogleAnalytics">
    <button className="closeButtonGlobal" onClick={onClosed}>❌</button>
    <div className="CadreAddGoogleAnalytics">
      <h2>Configurer Google Analytics pour un client</h2>
      <form onSubmit={handleSubmit}>
        <label>Clé API identifiants :</label>
        <input
          type="text"
          name="identifiantsAPI"
          value={formData.identifiantsAPI}
          onChange={handleChange}
          required
        />

        <label>Clé API Places:</label>
        <input
          type="text"
          name="PlacesAPI"
          value={formData.PlacesAPI}
          onChange={handleChange}
          required
        />
        <button type="submit">Enregistrer</button>
      </form>
    </div>
  </div>
);

};

export default AddGoogleAnalytics;
