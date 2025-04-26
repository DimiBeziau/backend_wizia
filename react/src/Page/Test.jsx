import React, { useState } from "react";

//AIzaSyCESK0F4bT8ShujpjV9t1IE1xOBRyoRer8   apiKey

const Test = () => {
  const [rating, setRating] = useState(null);

  const fetchRating = async () => {
    const placeId = 'ChIJra6o8IHuBUgRMO0NHlI3DQQ'; // Place ID pour Nantes
    const apiKey = 'AIzaSyCESK0F4bT8ShujpjV9t1IE1xOBRyoRer8';  // Remplace par ta clé API Google

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
    <div>
      <h1>Note de l'entreprise</h1>
      <button onClick={fetchRating}>Obtenir la note</button>
      {rating !== null && (
        <div>
          <p>⭐ {rating} / 5</p>
        </div>
      )}
    </div>
  );
};

export default Test;