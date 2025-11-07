function BtnCardVoirPlus({ text = "Voir le calendrier complet", onClick }) {
  const handleClick = (e) => {
    if (onClick) {
      onClick(e);
    }
  };

  return (
    <button
      onClick={handleClick}
      className="flex items-center justify-center w-full font-normal leading-normal text-center cursor-pointer md:max-w-[748px]"
      style={{
        backgroundColor: 'var(--color-default-grey-9)',
        border: '1px solid var(--color-default-grey-5)',
        borderRadius: 'var(--spacing-m)',
        paddingLeft: 'var(--spacing-m)',
        paddingRight: 'var(--spacing-m)',
        paddingTop: 'var(--spacing-s)',
        paddingBottom: 'var(--spacing-s)',
        fontSize: 'var(--font-size-h3)',
        color: 'var(--color-default-grey-1)',
        transition: 'opacity 0.2s'
      }}
    >
      {text}
    </button>
  );
}

export default BtnCardVoirPlus;