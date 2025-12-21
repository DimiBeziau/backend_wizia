function Card({ children, className = "" }) {
  return (
    <div
      className={`flex flex-col items-start justify-center w-full ${className} md:max-w-[748px]`}
      style={{
        backgroundColor: 'var(--color-default-grey-9)',
        border: '1px solid var(--color-default-grey-3)',
        borderRadius: 'var(--spacing-m)',
        paddingLeft: 'var(--spacing-m)',
        paddingRight: 'var(--spacing-m)',
        paddingTop: 'var(--spacing-m)',
        paddingBottom: 'var(--spacing-m)',
        gap: 'var(--spacing-m)'
      }}
    >
      {children}
    </div>
  );
}

export default Card;
