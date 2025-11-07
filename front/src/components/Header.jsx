import { MenuOutlined } from '@ant-design/icons'

function Header() {
  return (
    <div
      className="flex items-center justify-between w-full"
      style={{
        paddingLeft: 'var(--spacing-l)',
        paddingRight: 'var(--spacing-l)',
        paddingTop: '10px',
        paddingBottom: '10px'
      }}
    >
      <h2
        className="flex-1 text-center font-normal leading-normal"
        style={{
          fontSize: 'var(--font-size-h2)',
          color: 'var(--color-default-white)'
        }}
      >
        Wizia
      </h2>
      <div className="flex items-center justify-center shrink-0">
        <div className="rotate-90">
          <MenuOutlined
            style={{
              fontSize: '27px',
              color: 'var(--color-default-grey-2)'
            }}
          />
        </div>
      </div>
    </div>
  );
}

export default Header;