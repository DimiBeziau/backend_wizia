import React, { useState } from "react";
import { DateRange } from 'react-date-range';
import 'react-date-range/dist/styles.css'; // Import des styles
import 'react-date-range/dist/theme/default.css';
//

const Test = () => {
   const [reviews, setReviews] = useState([]);

//   useEffect(() => {
    const fetchReviews = async () => {
      const response = await fetch(`https://maps.googleapis.com/maps/api/place/details/json?placeid=YOUR_PLACE_ID&key=YOUR_API_KEY`);
      const data = await response.json();
      setReviews(data.result.reviews);
    };

//     // fetchReviews();
//   }, []);

  return (
      <div>
          <h1>test</h1>
      {/* {reviews.map((review, index) => (
        <div key={index}>
          <h3>{review.author_name}</h3>
          <p>{review.text}</p>
          <p>Rating: {review.rating}</p>
        </div>
      ))} */}
    </div>
  );

};

export default Test;
