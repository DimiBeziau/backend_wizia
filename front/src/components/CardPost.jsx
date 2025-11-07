import { EditOutlined, CommentOutlined, HeartOutlined } from '@ant-design/icons'

function CardPost({ post }) {
  const now = new Date();
  let template
  if (new Date(post.datePost) > now && post.isValidated == 0) {
    template = 1;
  } else if (new Date(post.datePost) > now && post.isValidated == 1) {
    template = 2;
  } else if (new Date(post.datePost) < now && post.isPublished == 1) {
    template = 3;
  }

  function differenceEnJours(date1, date2) {
    const unJourEnMs = 1000 * 60 * 60 * 24;
    const differenceEnMs = Math.abs(date2.getTime() - date1.getTime());
    return Math.floor(differenceEnMs / unJourEnMs);
  }


  function differenceEnHeures(date1, date2) {
    const uneHeureEnMs = 1000 * 60 * 60;
    const differenceEnMs = Math.abs(date2.getTime() - date1.getTime());
    return Math.floor(differenceEnMs / uneHeureEnMs);
  }
  return (
    <div className="flex flex-row justify-between items-center w-full rounded-lg p-2 gap-5">
      <div className="flex flex-row justify-start items-center gap-3 p-2 bg-white w-3/5 border-1 border-solid border-black rounded-lg">
        <div className=" rounded-lg p-[1px] flex items-center justify-center">
          <img src={`/${post.network}.webp`} className="rounded-full min-h-[20px] max-h-[20px] min-w-[20px] max-w-[20px] object-cover block m-0 p-0" style={{ display: 'block', margin: 0, padding: 0 }} />
        </div>
        <p className="w-auto whitespace-nowrap overflow-hidden text-ellipsis text-sm">{post.post}</p>
      </div >
      <div className="flex flex-row justify-center items-center gap-2 w-2/5">
        {(template === 1 || template === 2) &&
          <>
            <div className="bg-black text-white px-[5px] rounded-lg text-sm">{((differenceEnJours(now, new Date(post.datePost)) === 0) ? (differenceEnHeures(now, new Date(post.datePost)) === 0 ? "Dans moins d'1h" : "Dans " + differenceEnHeures(now, new Date(post.datePost)) + " h") : "Dans " + differenceEnJours(now, new Date(post.datePost)) + " j")}</div>
            <div className="bg-black text-white text-sm rounded-full p-[3px] px-[6px]">
              <EditOutlined />
            </div>
          </>
        }
        {template === 3 &&
          <>
            <div className="bg-black text-white px-[5px] rounded-lg text-sm">{post.likes} <HeartOutlined /></div>
            <div className="bg-black text-white px-[5px] rounded-lg text-sm">{post.comments} <CommentOutlined /></div>
          </>}

      </div>
    </div >
  );
}

export default CardPost;