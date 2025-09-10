import { Button, ColorPicker, Upload, message } from "antd";
import { InboxOutlined } from '@ant-design/icons'

function Step4SignUp({ formData, setFormData, onPrevStep, onNextStep }) {
  const { Dragger } = Upload;
  const [messageApi, contextHolder] = message.useMessage();
  function notif(type, title, message) {
    messageApi.open({
      type: type,
      title: title,
      content: message,
    });
  }
  const props = {
    maxCount: 1,
    name: 'file',
    multiple: false,
    action: `${import.meta.env.VITE_API_BASE_URL}/users/uploadlogo`,
    beforeUpload: file => {
      const isPNG = file.type === 'image/png';
      if (!isPNG) {
        notif('error', 'Erreur', `${file.name} n'est pas un fichier png`);
      }
      return isPNG || Upload.LIST_IGNORE;
    },
    onChange(info) {
      const { status } = info.file;
      if (status !== 'uploading') {
        console.log(info.file, info.fileList);
      }
      if (status === 'done') {
        notif('success', 'Succès', `${info.file.name} chargé correctement.`);
        setFormData((prev) => ({ ...prev, logo: info.file.response.path }))
      } else if (status === 'error') {
        notif('error', 'Erreur', `${info.file.name} mal chargé.`);
      }
    },
    onDrop(e) {
      console.log('Dropped files', e.dataTransfer.files);
    },
  };

  function toHexColor(obj) {
    const { r, g, b } = obj.metaColor;

    // Conversion des valeurs en hexadécimal sur 2 chiffres
    const rHex = r.toString(16).padStart(2, "0");
    const gHex = g.toString(16).padStart(2, "0");
    const bHex = b.toString(16).padStart(2, "0");

    return `#${rHex}${gHex}${bHex}`;
  }
  return (
    <div className="flex flex-col max-w-[410px] px-5 py-5">
      {contextHolder}
      <p className="pt-5">Raconte-nous ton activité comme si tu l’expliquais à un client sympa. Ce que tu fais, ce que tu aimes, comment tu travailles… On s’occupe du reste.</p>
      <div className="flex flex-col my-3">
        <Dragger {...props}>
          <p className="ant-upload-drag-icon">
            <InboxOutlined />
          </p>
          <p className="ant-upload-text">Clique ici pour charger ton logo</p>
        </Dragger>
      </div>
      <div className="flex flex-col my-3 items-start">
        <label>Sélectionnez une couleur pour votre entreprise</label>
        <ColorPicker defaultValue="#1677ff" showText format='hex' value={formData.color} onChange={(ev) => { setFormData((prev) => ({ ...prev, color: toHexColor(ev) })) }} />
      </div>
      <div className="flex flex-row-reverse justify-between my-3">
        <Button onClick={() => { onNextStep() }} className="mb-3">Contiuer</Button>
        <Button danger onClick={() => { onPrevStep() }} className="mb-3">Précédent</Button>
      </div>
    </div>
  );
}

export default Step4SignUp;