import '../bootstrap'
import ForgotPassword from './components/ForgotPassword'
import ForgotPasswordVerify from './components/ForgotPasswordVerify'
import NewPassword from './components/NewPassword'

$(() => {
    new ForgotPassword()
    new ForgotPasswordVerify()
    new NewPassword()
})