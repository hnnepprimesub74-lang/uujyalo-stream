export default function InputError({ message, className = '', ...props }) {
    if (!message) {
        return null;
    }

    return (
        <p {...props} className={'text-sm text-red-400 ' + className}>
            {message}
        </p>
    );
}
