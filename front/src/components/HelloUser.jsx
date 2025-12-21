function HelloUser({ user }) {
  user.name = user.first_name
  return (
    <div
      className="flex flex-col items-start w-full md:max-w-[748px]"
      style={{
        gap: 'var(--spacing-m)',
        color: 'var(--color-default-white)'
      }}
    >
      <h1
        className="font-normal leading-normal w-full"
        style={{
          fontSize: 'var(--font-size-h1)'
        }}
      >
        Bonjour {user.name},
      </h1>
      <h2
        className="font-normal leading-normal w-full"
        style={{
          fontSize: 'var(--font-size-h2)'
        }}
      >
        Actions urgentes
      </h2>
    </div>
  );
}

export default HelloUser;