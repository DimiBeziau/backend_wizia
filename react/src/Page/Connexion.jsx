import './Style/Connexion.css'
import {useState} from 'react'
const Connexion =()=>{
    const [authState, setAuthState] = useState({
        Email: '',
        PassWord: '',
      });
      const [error,setError] = useState('');

      const handleChange = (e)=>{
        setAuthState(e.target.value);
      }
      const regexConformation=(Email)=>{
            const RegexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return RegexEmail.test(Email)
      }
    const Connections = async ()=>{
        try{
          if (Email != "" & PassWord != '') {
          //  elseif
            regexConformation(Email);
            
          }
        }catch(e){
            console.error('Erreur lors de la requête  :', e);
            setError('Une erreur s\'est produite. Veuillez réessayer.');
        }
    }
return(
   <div className="Connexion">
        <div className="CadreConnexion">
            <form>
            <h1>Connexion WIZIA</h1>
                <label>
                    Mail: 
                    <input type="text" 
                    className="Mail"
                    value={authState.Email}
                    onChangeText={ handleChange}
                    placeholder="Email"
                    />
                </label>
                <label>
                    Password: 
                    <input type="text" 
                    className="Password"
                    value={authState.PassWord}
                    onChange={handleChange}
                    placeholder="PassWord"
                    />
                </label>
                <label>
                <a>Mots de passe oublié</a>
                </label>
                <label>
                <a>créé mon compte</a>
                </label>
                {error=='' && <Text style={styles.errorText}>{error}</Text>}
                <button onClick={Connections}>Connexion</button>
            </form>
        </div>
   </div> 
 )
};
 export default Connexion;