import { Button, Input } from "antd";

const { TextArea } = Input;

function Step2SignUp({ formData, setFormData, onPrevStep, onNextStep }) {
  function validateForm() {
    if (!formData.activity) {
      alert("Veuillez remplir le champ d'activité.");
      return;
    }
    onNextStep();
  }
  return (
    <div className="flex flex-col max-w-[410px] px-5 py-5">
      <p className="pt-5">Dis-nous ce que tu fais, on adaptera les contenus à ton univers.</p>
      <div className="flex flex-col my-3">
        <Input variant="filled" placeholder="Ton domaine d'activité" value={formData.activity} onChange={(ev) => { setFormData((prev) => ({ ...prev, activity: ev.target.value })) }} />
      </div>
      <div className="flex flex-row-reverse justify-between my-3">
        <Button onClick={() => { validateForm() }} className="mb-3">Contiuer</Button>
        <Button danger onClick={() => { onPrevStep() }} className="mb-3">Précédent</Button>
      </div>
    </div>
  );
}

export default Step2SignUp;