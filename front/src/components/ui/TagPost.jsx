function TagPost({ variant = "Planifié" }) {
  const getVariantStyles = () => {
    if (variant === "brouillon") {
      return {
        backgroundColor: 'rgba(255, 186, 195, 0.2)', // --color-pink-5 avec opacité 0.2
        textColor: 'var(--color-pink-5)',
        text: 'Brouillon'
      };
    } else {
      return {
        backgroundColor: 'rgba(86, 224, 221, 0.2)', // --color-green-5 avec opacité 0.2
        textColor: 'var(--color-green-5)',
        text: 'Planifié'
      };
    }
  };

  const styles = getVariantStyles();

  return (
    <div
      className="flex items-center justify-center whitespace-nowrap font-normal leading-normal"
      style={{
        backgroundColor: styles.backgroundColor,
        paddingLeft: 'var(--spacing-l)',
        paddingRight: 'var(--spacing-l)',
        paddingTop: 'var(--spacing-xs)',
        paddingBottom: 'var(--spacing-xs)',
        borderRadius: 'var(--spacing-l)',
        fontSize: 'var(--font-size-price)',
        color: styles.textColor
      }}
    >
      {styles.text}
    </div>
  );
}

export default TagPost;