import React from 'react';

function RadioCalendar({ value, value1, value2, onChange }) {
  return (
    <div
      className="flex gap-[10px] items-center p-[10px] rounded-[var(--spacing-m)] w-full"
      style={{
        backgroundColor: 'var(--color-default-bg-color)'
      }}
    >
      {/* Bouton 1 */}
      <button
        onClick={() => onChange?.(value1)}
        className="flex-1 flex items-center justify-center p-[10px] rounded-[var(--spacing-s)] transition-all duration-200 cursor-pointer border-none"
        style={{
          backgroundColor: value === value1 ? 'var(--color-default-white)' : 'var(--color-default-bg-color)',
          color: value === value1 ? 'var(--color-default-black)' : 'var(--color-default-grey-2)',
          fontSize: 'var(--font-size-h3)',
          fontWeight: 400,
          lineHeight: 'normal',
        }}
      >
        {value1}
      </button>

      {/* Bouton 2 */}
      <button
        onClick={() => onChange?.(value2)}
        className="flex-1 flex items-center justify-center p-[10px] rounded-[var(--spacing-s)] transition-all duration-200 cursor-pointer border-none"
        style={{
          backgroundColor: value === value2 ? 'var(--color-default-white)' : 'var(--color-default-bg-color)',
          color: value === value2 ? 'var(--color-default-black)' : 'var(--color-default-grey-2)',
          fontSize: 'var(--font-size-h3)',
          fontWeight: 400,
          lineHeight: 'normal',
        }}
      >
        {value2}
      </button>
    </div>
  );
}

export default RadioCalendar;

