function LargeButton({ text = "Champs de texte", variant = "Primary", onClick }) {
  // Configuration des styles selon le variant
  const getVariantStyles = () => {
    const baseStyles = {
      padding: 'var(--spacing-m)',
      borderRadius: 'var(--spacing-m)',
      fontSize: 'var(--font-size-h3)',
      fontWeight: 600,
      cursor: onClick ? 'pointer' : 'default',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      gap: '10px',
      width: '100%',
      border: 'none',
      transition: 'opacity 0.2s'
    };

    switch (variant) {
      case "Primary":
        return {
          ...baseStyles,
          backgroundColor: 'var(--color-green-6)',
          border: '1px solid var(--color-default-grey-3)',
          color: 'var(--color-default-black)'
        };

      case "Secondary":
        return {
          ...baseStyles,
          backgroundColor: 'var(--color-pink-6)',
          border: '1px solid var(--color-default-grey-3)',
          color: 'var(--color-default-black)'
        };

      case "third":
        return {
          ...baseStyles,
          backgroundColor: 'var(--color-purple-6)',
          border: '1px solid var(--color-default-grey-3)',
          color: 'var(--color-default-black)'
        };

      case "Simple":
        return {
          ...baseStyles,
          backgroundColor: 'transparent',
          border: 'none',
          color: 'var(--color-default-grey-1)'
        };

      case "Primary-special":
        return {
          ...baseStyles,
          backgroundColor: 'var(--color-purple-6)',
          border: '1px solid var(--color-default-grey-3)',
          color: 'var(--color-default-black)'
        };

      case "Secondary-special":
        return {
          ...baseStyles,
          backgroundColor: 'var(--color-pink-6)',
          border: '1px solid var(--color-default-grey-3)',
          color: 'var(--color-default-black)'
        };

      case "Third-special":
        return {
          ...baseStyles,
          backgroundColor: 'var(--color-green-6)',
          border: '1px solid var(--color-default-grey-3)',
          color: 'var(--color-default-black)'
        };

      case "Variant8":
        return {
          ...baseStyles,
          backgroundColor: 'var(--color-default-grey-8)',
          border: 'none',
          color: 'var(--color-default-grey-1)'
        };

      default:
        return {
          ...baseStyles,
          backgroundColor: 'var(--color-green-6)',
          border: '1px solid var(--color-default-grey-3)',
          color: 'var(--color-default-black)'
        };
    }
  };

  const handleClick = (e) => {
    if (onClick) {
      onClick(e);
    }
  };

  return (
    <button
      onClick={handleClick}
      style={getVariantStyles()}
      className="font-semibold leading-normal text-center"
    >
      {text}
    </button>
  );
}

export default LargeButton;