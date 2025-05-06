import { useNavigate } from "react-router-dom";
import React, { useState } from "react";


const Google_Analytics = () => {
    const [error, setError] = useState('');
  const [rating, setRating] = useState(null);
  const navigate = useNavigate();
    const AjouterGoogleAnalytics = () => {
        try {
            navigate("/Dashboard/Google_Analytics/AddGoogle_Analytics")
        } catch (e) {
            setError("imposible de changé de page")
        }
  }
//   useEffect(() => {
//   const placeId = 'ChIJra6o8IHuBUgRMO0NHlI3DQQ';
//   const apiKey = 'VOTRE_CLE_API';
//   fetchRating(placeId, apiKey);
// }, []);
  const fetchRating = async (placeId,apiKey) => {
    // const placeId = 'ChIJra6o8IHuBUgRMO0NHlI3DQQ'; // Place ID pour Nantes
    // const apiKey = 'AIzaSyCESK0F4bT8ShujpjV9t1IE1xOBRyoRer8';  // Remplace par ta clé API Google

    try {
      const response = await fetch(`https://maps.googleapis.com/maps/api/place/details/json?place_id=${placeId}&fields=rating&key=${apiKey}`);
      const data = await response.json();

      if (data.result && data.result.rating) {
        setRating(data.result.rating);  // Stocke la note récupérée
      } else {
        console.log('Aucune note trouvée.');
      }
    } catch (error) {
      console.error('Error fetching rating:', error);
    }
  };
  return (
    <div className="Google_Analytics">
      <h2>Google Analytics</h2>
      <button onClick={AjouterGoogleAnalytics}>ajouter un Google Analytics</button>
      {/* {rating !== null && (
        <div>
          <p>⭐ {rating} / 5</p>
        </div>
      )} */}
    </div>
  );
};

export default Google_Analytics;
