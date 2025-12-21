import { Button, Input } from "antd";
const { TextArea } = Input;


function Step3SignUp({ formData, setFormData, onPrevStep, onNextStep }) {
  return (
    <div className="flex flex-col max-w-[410px] px-5 py-5">
      <p className="pt-5">Raconte-nous ton activité comme si tu l’expliquais à un client sympa. Ce que tu fais, ce que tu aimes, comment tu travailles… On s’occupe du reste.</p>
      <div className="flex flex-col my-3">
        <TextArea rows={3} variant="filled" placeholder="Décris ton activité" value={formData.description} onChange={(ev) => { setFormData((prev) => ({ ...prev, description: ev.target.value })) }} />
      </div>
      <div className="flex flex-row-reverse justify-between my-3">
        <Button onClick={() => { onNextStep() }} className="mb-3">Contiuer</Button>
        <Button danger onClick={() => { onPrevStep() }} className="mb-3">Précédent</Button>
      </div>
    </div>
  );
}

export default Step3SignUp;