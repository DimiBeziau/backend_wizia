import React, { useEffect, useState } from 'react';

const GoogleReviews = () => {
  const [reviews, setReviews] = useState([]);

  useEffect(() => {
    const fetchReviews = async () => {
      const response = await fetch(`https://maps.googleapis.com/maps/api/place/details/json?placeid=YOUR_PLACE_ID&key=YOUR_API_KEY`);
      const data = await response.json();
      setReviews(data.result.reviews);
    };

    fetchReviews();
  }, []);

  return (
    <div>
      {reviews.map((review, index) => (
        <div key={index}>
          <h3>{review.author_name}</h3>
          <p>{review.text}</p>
          <p>Rating: {review.rating}</p>
        </div>
      ))}
    </div>
  );
};