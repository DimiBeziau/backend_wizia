import React, { useState } from "react";
import { DateRange } from 'react-date-range';
import 'react-date-range/dist/styles.css'; 
import 'react-date-range/dist/theme/default.css';

const Marronniers = ({ onDateChange }) => {
  const [state, setState] = useState([
    {
      startDate: new Date(),
      endDate: new Date(),
      key: 'selection'
    }
  ]);

  const handleSelect = (ranges) => {
    const selection = ranges.selection;
    setState([{
      startDate: selection.startDate,
      endDate: selection.startDate,
      key: 'selection'
    }]);

    onDateChange({
      startDate: selection.startDate,
    });
  };

  return (
    <div>
      <DateRange
        onChange={handleSelect}
        showSelectionPreview={false}
        moveRangeOnFirstSelection={false}
        months={2}
        ranges={state}
        direction="horizontal"
        rangeColors={['#3d91ff']}
        editableDateInputs={true}
        minDate={new Date()}
      />
      {state.map((range) => (
        <div key={range.key}>
          <p>Date sélectionnée : {range.startDate.toLocaleDateString()}</p> 
        </div>
      ))}
    </div>
  );
};

export default Marronniers;
