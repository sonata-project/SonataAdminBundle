<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Command;

use Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper;
use Sensio\Bundle\GeneratorBundle\Command\Helper\QuestionHelper;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

abstract class QuestionableCommand extends ContainerAwareCommand
{
    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param string          $questionText
     * @param mixed           $default
     * @param callable        $validator
     *
     * @return mixed
     */
    final protected function askAndValidate(InputInterface $input, OutputInterface $output, $questionText, $default, $validator)
    {
        $questionHelper = $this->getQuestionHelper();

        // NEXT_MAJOR: Remove this BC code for SensioGeneratorBundle 2.3/2.4 after dropping support for Symfony 2.3
        if ($questionHelper instanceof DialogHelper) {
            return $questionHelper->askAndValidate(
                $output,
                $questionHelper->getQuestion($questionText, $default),
                $validator,
                false,
                $default
            );
        }

        $question = new Question($questionHelper->getQuestion($questionText, $default), $default);

        $question->setValidator($validator);

        return $questionHelper->ask($input, $output, $question);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param string          $questionText
     * @param string          $default
     * @param string          $separator
     *
     * @return string
     */
    final protected function askConfirmation(InputInterface $input, OutputInterface $output, $questionText, $default, $separator)
    {
        $questionHelper = $this->getQuestionHelper();

        // NEXT_MAJOR: Remove this BC code for SensioGeneratorBundle 2.3/2.4 after dropping support for Symfony 2.3
        if ($questionHelper instanceof DialogHelper) {
            $question = $questionHelper->getQuestion($questionText, $default, $separator);

            return $questionHelper->askConfirmation($output, $question, ($default === 'no' ? false : true));
        }

        $question = new ConfirmationQuestion($questionHelper->getQuestion(
            $questionText,
            $default,
            $separator
        ), ($default === 'no' ? false : true));

        return $questionHelper->ask($input, $output, $question);
    }

    /**
     * @return QuestionHelper|DialogHelper
     */
    final protected function getQuestionHelper()
    {
        // NEXT_MAJOR: Remove this BC code for SensioGeneratorBundle 2.3/2.4 after dropping support for Symfony 2.3
        if (class_exists('Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper')) {
            $questionHelper = $this->getHelper('dialog');

            if (!$questionHelper instanceof DialogHelper) {
                $questionHelper = new DialogHelper();
                $this->getHelperSet()->set($questionHelper);
            }
        } else {
            $questionHelper = $this->getHelper('question');

            if (!$questionHelper instanceof QuestionHelper) {
                $questionHelper = new QuestionHelper();
                $this->getHelperSet()->set($questionHelper);
            }
        }

        return $questionHelper;
    }
}
