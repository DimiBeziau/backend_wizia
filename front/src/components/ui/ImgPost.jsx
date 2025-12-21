import stars1 from "../../assets/stars-1.svg";
import stars2 from "../../assets/stars-2.svg";
import lightBulb from "../../assets/light-bulb.svg";

function ImgPost({ variant = "planifié", img = "stars" }) {
  const getVariantStyles = () => {
    if (variant === "brouillon") {
      return {
        backgroundColor: 'rgba(255, 186, 195, 0.2)', // --color-pink-5 avec opacité 0.2
        vectorColor: 'var(--color-pink-5)'
      };
    } else {
      return {
        backgroundColor: 'rgba(160, 154, 234, 0.2)', // --color-purple-6 avec opacité 0.2
        vectorColor: 'var(--color-purple-6)'
      };
    }
  };

  const styles = getVariantStyles();

  const getImageSrc = () => {
    if (img === "bulb") {
      return lightBulb;
    } else {
      // img === "stars" (par défaut)
      return variant === "brouillon" ? stars2 : stars1;
    }
  };

  const getImageAlt = () => {
    if (img === "bulb") {
      return "light bulb";
    } else {
      return "stars";
    }
  };

  return (
    <div
      className="flex items-center justify-center w-[50px] h-[50px]"
      style={{
        backgroundColor: styles.backgroundColor,
        padding: 'var(--spacing-s)',
        borderRadius: 'var(--spacing-m)'
      }}
    >
      <img
        src={getImageSrc()}
        alt={getImageAlt()}
        style={{
          width: '100%',
          height: '100%',
          objectFit: 'contain'
        }}
      />

    </div>
  );
}

export default ImgPost;