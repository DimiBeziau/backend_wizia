function FilterTag({ filterValue, setFilterValue, value, text }) {
  const isActive = filterValue === value;

  const handleClick = () => {
    setFilterValue(value);
  };

  return (
    <button
      onClick={handleClick}
      className="flex items-center justify-center whitespace-nowrap font-normal leading-normal cursor-pointer"
      style={{
        backgroundColor: isActive ? 'var(--color-purple-8)' : 'var(--color-default-grey-8)',
        paddingLeft: 'var(--spacing-l)',
        paddingRight: 'var(--spacing-l)',
        paddingTop: 'var(--spacing-xs)',
        paddingBottom: 'var(--spacing-xs)',
        borderRadius: 'var(--spacing-s)',
        fontSize: 'var(--font-size-h4)',
        color: 'var(--color-default-white)',
        border: 'none',
        transition: 'background-color 0.2s'
      }}
    >
      {text || value}
    </button>
  );
}

export default FilterTag;