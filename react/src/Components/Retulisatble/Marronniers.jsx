import React, { useState } from "react";
import { Calendar } from 'react-date-range';
import 'react-date-range/dist/styles.css';
import 'react-date-range/dist/theme/default.css';

const Marronniers = ({ onDateChange }) => {
  const [date, setDate] = useState(new Date());

  const handleSelect = (_date) => {
    const selection = _date;
    setDate(selection);

    onDateChange({
      startDate: selection,
    });
  };

  return (
    <div>
      <Calendar
        onChange={handleSelect}
        showSelectionPreview={false}
        moveRangeOnFirstSelection={false}
        months={2}
        date={date}
        direction="horizontal"
        rangeColors={['#3d91ff']}
        editableDateInputs={true}
        minDate={new Date()}
      />
      {date &&
        <div>
          <p>Date sélectionnée : {date.toLocaleDateString()}</p>
        </div>
      }
    </div>
  );
};

export default Marronniers;
